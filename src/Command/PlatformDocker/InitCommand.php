<?php

namespace tes\CmsBuilder\Command\PlatformDocker;

use mglaman\PlatformDocker\Command\InitCommand as PDInitCommand;
use mglaman\PlatformDocker\Config;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sets up Platform and Docker Compose files.
 *
 * Depends on docker.
 */
class InitCommand extends PDInitCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('platform-docker:init')
            // Need to alias this to init to ensure it can run if required.
            ->setAliases(['init'])
            ->setDescription('Sets up Platform and Docker Compose files');
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        if (empty(Config::get())) {
            $this->cwd = getcwd();

            Config::set('alias-group', basename($this->cwd));
            Config::set('name', basename($this->cwd));
            Config::set('path', $this->cwd);

            // Hard code to current platform standard.
            Config::set('docroot', '_www');
            if (!Config::write($this->cwd)) {
                throw new \Exception('There was an error writing the platform configuration.');
            }
        }
        parent::initialize($input, $output);
    }

}