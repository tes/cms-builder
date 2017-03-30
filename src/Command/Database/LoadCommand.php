<?php

namespace tes\CmsBuilder\Command\Database;

use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use tes\CmsBuilder\Application;
use tes\CmsBuilder\Config;

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
        if (empty(Config::get('database'))) {
            $output->writeln('<info>No database to load as \'database\' key not set in .cms-builder.yml</info>');
            return;
        }

        if (!Application::databaseServerAvailable($output)) {
            $output->writeln("<error>Database server not available</error>");
            return 1;
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
