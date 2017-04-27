<?php

namespace tes\CmsBuilder\Command;

use mglaman\Docker\Compose;
use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * Builds a Tes site.
 *
 * Depends on docker, platform.sh cli, curl, gunzip, drush, mysql.
 */
class BuildCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('build')
            ->addArgument('site', InputArgument::OPTIONAL, 'Builds a specific site if there repository has multiple')
            ->setDescription('Builds a working site from a clone of a CMS repo from TES github.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->chooseSite($input, $output);
        $stopwatch = new Stopwatch();
        $stopwatch->start('build');
        /** @var \Symfony\Component\Console\Command\Command[] $commands */
        $commands = [];
        // Build the code base.
        $commands[] = $this->getApplication()->find('platform:build');
        // Build the docker containers.
        $commands[] = $this->getApplication()->find('platform-docker:init');
        $commands[] = $this->getApplication()->find('database:get');
        $commands[] = $this->getApplication()->find('database:load');
        $commands[] = $this->getApplication()->find('post-build');

        foreach ($commands as $command) {
            $return = $command->run($input, $output);
            if ($return !== 0) {
                $output->writeln('<error>Command '. $command->getName() . ' failed</error>');
                return $return;
            }
        }
        $event = $stopwatch->stop('build');
        $output->writeln(sprintf(
            '<info>Build completed in %s seconds using %s MB</info>',
            number_format($event->getDuration()/1000, 2),
            number_format($event->getMemory() / 1048576, 2)
        ));

        // Ensure the site is actually ready to use.
        $container_name = Compose::getContainerName(Platform::projectName(), 'phpfpm');
        $check = TRUE;
        $output->writeln('Copying files to containers. <comment>This might take sometime.</comment>');
        $times = 0;
        $stopwatch->start('check');
        while ($check) {
            try {
              Docker::sh($container_name, 'ls /var/www/html/web/index.php');
              $check = FALSE;
            }
            catch (\Exception $e) {
                sleep(10);
                $times++;
            }
            if ($times > 12) {
                $output->writeln('<error>Waited for 2 minutes and files still not available</error>');
                $check = FALSE;
            }
        }
        $event = $stopwatch->stop('check');
        $output->writeln(sprintf('<info>File sync completed in %s seconds</info>', number_format($event->getDuration()/1000, 2)));
        return 0;
    }


}
