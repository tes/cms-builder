<?php

namespace tes\CmsBuilder;

use mglaman\PlatformDocker\Command\Docker\RebuildCommand;
use mglaman\PlatformDocker\Command\Docker\StopCommand;
use mglaman\PlatformDocker\Command\Docker\UpCommand;
use mglaman\PlatformDocker\Command\DrushCommand;
use mglaman\PlatformDocker\Command\LinkCommand;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Application as ParentApplication;
use Symfony\Component\Console\Helper\DebugFormatterHelper;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\ProcessHelper;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Filesystem\Filesystem;

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
        $commands[] = new Command\Platform\BuildCommand();
        $commands[] = new Command\PlatformDocker\InitCommand();
        $commands[] = new LinkCommand();
        $commands[] = new DrushCommand();
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

}
