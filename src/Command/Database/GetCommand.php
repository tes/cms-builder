<?php

namespace tes\CmsBuilder\Command\Database;

use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use tes\CmsBuilder\Config;

class GetCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('database:get')
          ->setDescription('Gets a database backup');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $remote = Config::get('database');
        $local = Platform::rootDir() . '/.cms-builder/database.tar.gz';
        $this->getApplication()->ensureDirectory();

        if (file_exists($local)) {
            $curl = curl_init($remote);
            // Don't fetch the actual page, you only want headers.
            curl_setopt($curl, CURLOPT_NOBODY, true);
            // Stop it from outputting stuff to stdout.
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            // Attempt to retrieve the modification date.
            curl_setopt($curl, CURLOPT_FILETIME, true);
            $result = curl_exec($curl);
            if ($result === false) {
                $output->writeln('<error>Curl error</error>: ' . curl_error($curl));
                return;
            }
            $remote_timestamp = curl_getinfo($curl, CURLINFO_FILETIME);
            $local_timestamp = filemtime($local);

            if ($remote_timestamp !== -1 && $local_timestamp > $remote_timestamp) {
                $output->writeln("<info>The local database dump ($local) is newer than the remote. If you want force it to be downloaded, delete the local file.</info>");
                return;
            }
        }

        $output->writeln("<info>Downloading database from $remote</info>");
        $builder = ProcessBuilder::create([
            'curl',
            $remote,
            '-o',
            $local
        ]);
        $builder->setTimeout(null);
        $builder->enableOutput();
        $process = $builder->getProcess();
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
