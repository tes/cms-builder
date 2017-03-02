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
        // See if the nginx-proxy is running and use that if it is.
        // @todo this health check is not working.
        $db_container = Compose::getContainerName(Platform::projectName(), 'mariadb');
        $process = Docker::inspect(['--format="{{ .State.Running }}"', $db_container], true);
        $retry = 0;
        while (strpos($process->getOutput(), 'true') === FALSE) {
            sleep(10);
            $retry++;
            if ($retry > 5) {
                $output->writeln("<error>Database server not available</error>");
                return 1;
            }
            $process = Docker::inspect(['--format="{{ .State.Running }}"', $db_container], true);
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
