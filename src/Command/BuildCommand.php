<?php

namespace tes\CmsBuilder\Command;

use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
          ->setDescription('Builds a working site from a clone of a CMS repo from TES github.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \Symfony\Component\Console\Command\Command[] $commands */
        $commands = [
            $this->getApplication()->find('platform:build'),
            $this->getApplication()->find('platform-docker:init'),
            $this->getApplication()->find('database:get'),
            $this->getApplication()->find('database:load'),
            $this->getApplication()->find('post-build')
        ];

        foreach ($commands as $command) {
            $return = $command->run($input, $output);
            if ($return !== 0) {
                $output->writeln('<error>Command '. $command->getName() . ' failed</error>');
                return $return;
            }
        }
        return 0;
    }


}
