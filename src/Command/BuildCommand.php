<?php

namespace tes\CmsBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
        ];

        foreach ($commands as $command) {
            $return = $command->run($input, $output);
            if ($return !== 0) {
                $output->writeln('<error>Command '. $command->getName() . ' failed</error>');
                return $return;
            }
        }
    }


}
