<?php

namespace tes\CmsBuilder;

use mglaman\Docker\Compose;
use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Command\Docker\RebuildCommand;
use mglaman\PlatformDocker\Command\Docker\StopCommand;
use mglaman\PlatformDocker\Command\Docker\UpCommand;
use mglaman\PlatformDocker\Command\DrushCommand;
use mglaman\PlatformDocker\Command\LinkCommand;
use mglaman\PlatformDocker\Platform;
use mglaman\PlatformDocker\Mysql\Mysql;
use Symfony\Component\Console\Application as ParentApplication;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Filesystem\Filesystem;
use tes\CmsBuilder\Command\ConfigFiles;
use tes\CmsBuilder\Command\SelfUpdateCommand;

/**
 * The cms-builder application.
 */
class Application extends ParentApplication
{
    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        parent::__construct('CMS Builder', '0.0.1');
        $this->setDefaultTimezone();
        $this->addCommands($this->getCommands());
    }

    /**
     * @return \Symfony\Component\Console\Command\Command[]
     */
    public function getCommands()
    {
        static $commands = array();
        if (count($commands)) {
            return $commands;
        }
        $commands[] = new Command\Database\GetCommand();
        $commands[] = new Command\Database\LoadCommand();
        $commands[] = new Command\BuildCommand();
        $commands[] = new Command\PostBuildCommand();
        $commands[] = new ConfigFiles();
        $commands[] = new Command\Platform\BuildCommand();
        $commands[] = new Command\PlatformDocker\InitCommand();
        $commands[] = new LinkCommand();
        $commands[] = new DrushCommand();
        $commands[] = new SelfUpdateCommand();
        // Add Platform Docker commands
        $commands[] = new Command\CommandWrapper(new UpCommand());
        $commands[] = new Command\CommandWrapper(new StopCommand());
        $commands[] = new Command\CommandWrapper(new RebuildCommand());
        return $commands;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultHelperSet()
    {
        return new HelperSet(array(
          new FormatterHelper(),
          new DebugFormatterHelper(),
          new ProcessHelper(),
          new QuestionHelper(),
        ));
    }


    /**
     * Set the default timezone.
     *
     * PHP 5.4 has removed the autodetection of the system timezone,
     * so it needs to be done manually.
     * UTC is the fallback in case autodetection fails.
     */
    protected function setDefaultTimezone()
    {
        $timezone = 'UTC';
        if (is_link('/etc/localtime')) {
            // Mac OS X (and older Linuxes)
            // /etc/localtime is a symlink to the timezone in /usr/share/zoneinfo.
            $filename = readlink('/etc/localtime');
            if (strpos($filename, '/usr/share/zoneinfo/') === 0) {
                $timezone = substr($filename, 20);
            }
        } elseif (file_exists('/etc/timezone')) {
            // Ubuntu / Debian.
            $data = file_get_contents('/etc/timezone');
            if ($data) {
                $timezone = trim($data);
            }
        } elseif (file_exists('/etc/sysconfig/clock')) {
            // RHEL/CentOS
            $data = parse_ini_file('/etc/sysconfig/clock');
            if (!empty($data['ZONE'])) {
                $timezone = trim($data['ZONE']);
            }
        }
        date_default_timezone_set($timezone);
    }

    public static function getCmsBuilderDirectory() {
        $directory = self::getUserDirectory() . '/.cms-builder/' . Platform::projectName();
        if (!is_dir($directory)) {
            $file_system = new Filesystem();
            $file_system->mkdir($directory);
        }
        return $directory;
    }

    /**
     * @return string The formal user home as detected from environment parameters
     * @throws \RuntimeException If the user home could not reliably be determined
     */
    public static function getUserDirectory()
    {
        if (false !== ($home = getenv('HOME'))) {
            return $home;
        }
        if (defined('PHP_WINDOWS_VERSION_BUILD') && false !== ($home = getenv('USERPROFILE'))) {
            return $home;
        }
        if (function_exists('posix_getuid') && function_exists('posix_getpwuid')) {
            $info = posix_getpwuid(posix_getuid());
            return $info['dir'];
        }
        throw new \RuntimeException('Could not determine user directory');
    }

    /**
     * Determines if the database server is available.
     *
     * @return bool
     *   TRUE if the database is available, FALSE if not.
     */
    public static function databaseServerAvailable(OutputInterface $output) {
        // Get the docker database port.
        $db_container = Compose::getContainerName(Platform::projectName(), 'mariadb');
        $inspect = Docker::inspect(['--format=\'{{(index (index .NetworkSettings.Ports "3306/tcp") 0).HostPort}}\'', $db_container], true);

        if (!$inspect->isSuccessful()) {
            $output->writeln("<error>Command failed:</error> " . $inspect->getCommandLine());
            if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
                $output->writeln("Error output: " . $inspect->getErrorOutput());
                $output->writeln("STD output: " . $inspect->getOutput());
            }
            return FALSE;
        }

        preg_match('!\d+!', $inspect->getOutput(), $matches);
        $port = $matches[0];

        // Ensure that we can connect to the database before loading the data.
        $dsn = "mysql:dbname=data;host=127.0.0.1;port=$port;";
        $retry = 0;
        while (TRUE) {
            if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
                $output->writeln("<info>Trying to connect to database using $dsn user:" . Mysql::getMysqlUser() . " pass:" . Mysql::getMysqlPassword());
            }
            try {
                new \PDO($dsn, Mysql::getMysqlUser(), Mysql::getMysqlPassword());
                break;
            }
            catch (\Exception $e) {
                if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
                    $output->writeln('<error>' . $e->getMessage() . '</error>');
                }
            }
            sleep(10);
            $retry++;
            if ($retry > 5) {
                return FALSE;
            }
        }
        return TRUE;
    }

    /**
     * Prompt the user to choose a site if necessary.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function chooseSite(InputInterface $input, OutputInterface $output) {
        // If it is already set just return.
        if (Config::getSite()) {
            return;
        }
        // What sites are available to build?
        $sites = Config::findSites();
        if (!empty($sites)) {
            $site = $input->getArgument('site');
            if (!in_array($site, $sites, TRUE) && count($sites) > 1) {
                $helper = new QuestionHelper();
                $question = new ChoiceQuestion('Which site do you want to build?', $sites);
                $site = $helper->ask($input, $output, $question);
            }
            else {
                // Either we've supplied a correct argument or There's only one site use that one.
                $site = $input->getArgument('site') ?: reset($sites);
            }
            Config::setSite($site);
        }
    }

}
