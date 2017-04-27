<?php

namespace tes\CmsBuilder\Command;

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
        return 0;
    }


}
