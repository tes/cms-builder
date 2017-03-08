<?php

namespace tes\CmsBuilder\Command\Database;

use mglaman\Docker\Compose;
use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use tes\CmsBuilder\Application;

/**
 * Loads a database dump.
 *
 * Depends on gunzip, drush and mysql.
 */
class LoadCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('database:load')
          ->setDescription('Loads a database backup');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get the docker database port.
        $db_container = Compose::getContainerName(Platform::projectName(), 'mariadb');
        $inspect = Docker::inspect(['--format=\'{{(index (index .NetworkSettings.Ports "3306/tcp") 0).HostPort}}\'', $db_container], true);
        preg_match('!\d+!', $inspect->getOutput(), $matches);
        $port = $matches[0];

        // Ensure that we can connect to the database before loading the data.
        $dsn = "mysql:dbname=data;host=127.0.0.1:$port;";
        $retry = 0;
        while (TRUE) {
            try {
                new \PDO($dsn, 'mysql', 'mysql');
                break;
            }
            catch (\Exception $e) {
            }
            sleep(10);
            $retry++;
            if ($retry > 5) {
                $output->writeln("<error>Database server not available</error>");
                return 1;
            }
        }

        $local = Application::getCmsBuilderDirectory() . '/database.tar.gz';

        if (!file_exists($local)) {
            $this->getApplication()->find('database:get')->run($input, $output);
        }

        $output->writeln("<info>Importing database from $local</info>");
        $process = new Process("gunzip -c $local | `drush sql-connect`", Platform::webDir(), null, null, null);
        if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
            $output->writeln($process->getCommandLine());
        }
        $function  = function ($type, $buffer) use ($output, $process) {
            $output->write($buffer);
        };

        $process->start($function);
        $process->wait();
    }

}
