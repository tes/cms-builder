<?php

namespace tes\CmsBuilder\Command;

use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

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
        ];

        foreach ($commands as $command) {
            $return = $command->run($input, $output);
            if ($return !== 0) {
                $output->writeln('<error>Command '. $command->getName() . ' failed</error>');
                return $return;
            }
        }

        // @todo determine health of mysql server somehow.
        sleep(20);
        $this->getApplication()->find('database:load')->run($input, $output);

        // Enable stage_file_proxy and devel modules.
        $process = new Process("drush en stage_file_proxy devel -y", Platform::webDir(), null, null, null);
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
