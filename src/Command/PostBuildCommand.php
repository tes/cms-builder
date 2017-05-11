<?php

namespace tes\CmsBuilder\Command;

use mglaman\Docker\Compose;
use mglaman\Docker\Docker;
use mglaman\PlatformDocker\DrushDiscovery;
use mglaman\PlatformDocker\Platform;
use mglaman\PlatformDocker\Stacks\StacksFactory;
use mglaman\Toolstack\Toolstack;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use tes\CmsBuilder\Application;
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
            ->addArgument('site', InputArgument::OPTIONAL, 'Builds a specific site if there repository has multiple')
            ->setDescription('Runs the post-build commands based on the content of a project\'s .cms-builder.yml.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->getApplication()->chooseSite($input, $output);
        if (!Application::databaseServerAvailable($output)) {
            $output->writeln("<error>Database server not available</error>");
            return 1;
        }

        // Rebuild all the configuration files so we can swap out the install profile if necessary.
        $stack = Toolstack::inspect(Platform::webDir());
        if ($stack) {
            $output->writeln("<comment>Configuring stack:</comment> " . $stack->type());
            StacksFactory::configure($stack->type());
        }

        // Run the config-file command.
        $commands[] = $this->getApplication()->find('config-files');
        foreach ($commands as $command) {
            $return = $command->run($input, $output);
            if ($return !== 0) {
                $output->writeln('<error>Command '. $command->getName() . ' failed</error>');
                return $return;
            }
        }

        // Run post build commands.
        $post_build_cmds = Config::get('post_build') ?: [];
        $post_build_cmds = $post_build_cmds + ['bash' => [], 'docker' => [], 'drush' => []];

        // Function to pass to \Symfony\Component\Process\Process::start() so we can see the output.
        $function  = function ($type, $buffer) use ($output) {
            $output->write($buffer);
        };

        foreach ($post_build_cmds['bash'] as $command) {
            $process = new Process($command, Platform::rootDir());
            if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
                $output->writeln($process->getCommandLine());
            }
            $process->start($function);
            $process->wait();
        }
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
        $drush = DrushDiscovery::getExecutable();
        foreach ($post_build_cmds['drush'] as $command) {
            $process = new Process("$drush $command", Platform::webDir(), null, null, null);
            if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
                $output->writeln($process->getCommandLine());
            }
            $process->start($function);
            $process->wait();
        }
        // Ensure that we can modify settings.php. After site-installs Drupal will chmod the directory that makes
        // managing settings.php in git hard. As cms-builder is only intended for dev builds and protected environments
        // this should be ok.
        $fs = new Filesystem();
        $fs->chmod(Platform::webDir() . '/sites/default', 0775);
    }


}
