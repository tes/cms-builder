<?php

namespace tes\CmsBuilder\Command\Platform;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Runs the platform build command.
 *
 * Depends on platform.sh cli tool.
 */
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
            // Ensure that composer build runs every time.
            '--no-archive',
            '--source=.',
            '--destination=_www'
        ]);
        $builder->setTimeout(null);
        $builder->enableOutput();
        $process = $builder->getProcess();
        $output->writeln('<info>Running platform build</info>');
        if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
            $output->writeln($process->getCommandLine());
        }
        $function  = function ($type, $buffer) use ($output, $process) {
            $output->write($buffer);
        };
        $run_again = clone $process;
        $process->start($function);
        $process->wait();

        // Unfortunately platform build does not always get the symlinks correct the first time.
        // @see https://github.com/platformsh/platformsh-cli/issues/578
        $run_again->start($function);
        $run_again->wait();
    }
}
