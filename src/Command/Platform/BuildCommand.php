<?php

namespace tes\CmsBuilder\Command\Platform;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class BuildCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('platform:build')
          ->setDescription('Runs the platform build command');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // @todo validate that platform.sh's CLI tool is installed.
        // @todo how to provide feedback whilst the process is on going?
        $builder = ProcessBuilder::create([
            'platform',
            'build',
            '--source=.',
            '--destination=_www'
        ]);
        $builder->setTimeout(null);
        $builder->enableOutput();
        $process = $builder->getProcess();
        $process->run();

        if (!$process->isSuccessful()) {
            $output->writeln($process->getErrorOutput());
        }
        else {
            $output->write($process->getOutput());
        }
    }
}
