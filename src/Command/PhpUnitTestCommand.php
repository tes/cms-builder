<?php

namespace tes\CmsBuilder\Command;

use mglaman\Docker\Compose;
use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Runs tests using PHPUnit.
 *
 * Depends on docker.
 */
class PhpUnitTestCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('phpunit')
            ->setDescription('Runs phpunit on the phpfpm container.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = Compose::getContainerName(Platform::projectName(), 'phpfpm');
        // Function to pass to \Symfony\Component\Process\Process::start() so we can see the output.
        $function  = function ($type, $buffer) use ($output) {
            $output->write($buffer);
        };
        $process = Docker::sh($container, 'cd /var/platform && vendor/bin/phpunit', $function);
        return $process->getExitCode();
    }

}
