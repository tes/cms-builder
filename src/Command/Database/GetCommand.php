<?php

namespace tes\CmsBuilder\Command\Database;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;
use tes\CmsBuilder\Application;
use tes\CmsBuilder\Config;

/**
 * Gets a database from the Tes jenkins server.
 *
 * Depends on curl.
 */
class GetCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('database:get')
          ->addArgument('site', InputArgument::OPTIONAL, 'Builds a specific site if there repository has multiple')
          ->setDescription('Gets a database backup');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->chooseSite($input, $output);
        $remote = Config::get('database');
        if (empty($remote)) {
            $output->writeln('<info>No remote database to get as \'database\' key not set in .cms-builder.yml</info>');
            return;
        }
        $local = static::getBackupDbPath(Config::getSite());

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
                $output->writeln("<info>Builds will use the existing database dump ($local). In order to download a new dump you might need to VPN into the TES network.</info>");
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

    /**
     * Get the path to a site's backup database.
     *
     * @param string|null $site
     *   (optional) The name of the site the database is for, if it has one.
     *
     * @return string
     *   The absolute path to the database backup.
     *
     * @see Config::getSite()
     */
    public static function getBackupDbPath($site = null) {
        $name = 'database';
        if ($site) {
            $name .= ".$site";
        }
        $name .= '.sql.gz';
        return Application::getCmsBuilderDirectory() . "/$name";
    }

}
