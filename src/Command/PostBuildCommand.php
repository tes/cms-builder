<?php

namespace tes\CmsBuilder\Command;

use mglaman\Docker\Compose;
use mglaman\Docker\Docker;
use mglaman\PlatformDocker\Platform;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use tes\CmsBuilder\Config;

/**
 * Builds a Tes site.
 *
 * Depends on docker and drush.
 */
class PostBuildCommand extends Command
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
          ->setName('post-build')
          ->setDescription('Runs the post-build commands based on the content of a project\'s .cms-builder.yml.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Run post build commands.
        $post_build_cmds = Config::get('post_build') + ['docker' => [], 'drush' => []];

        // Function to pass to \Symfony\Component\Process\Process::start() so we can see the output.
        $function  = function ($type, $buffer) use ($output) {
            $output->write($buffer);
        };

        foreach ($post_build_cmds['docker'] as $container => $commands) {
            $container_name = Compose::getContainerName(Platform::projectName(), $container);
            foreach ($commands as $command) {
                Docker::sh($container_name, $command, $function);
                if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
                    $output->writeln("<info>Running command on $container_name:</info> $command");
                }
            }
            // Restart the container.
            Docker::stop([$container_name]);
            Docker::start([$container_name]);
        }
        foreach ($post_build_cmds['drush'] as $command) {
            $process = new Process("drush $command", Platform::webDir(), null, null, null);
            if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
                $output->writeln($process->getCommandLine());
            }
            $process->start($function);
            $process->wait();
        }
    }


}
