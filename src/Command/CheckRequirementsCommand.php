<?php

namespace tes\CmsBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\ProcessBuilder;

class CheckRequirementsCommand extends Command
{

    const PLATFORM_VERSION = '3.16.0';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('check-requirements')
            ->setDescription('Checks the requirements');
    }


    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this::checkPlatformShCli($output);
    }

    /**
     * Checks the Platform.sh requirement.
     *
     * @param $output
     *
     * @throws \RuntimeException
     *   Thrown if the requirement is not met.
     */
    public static function checkPlatformShCli($output) {
        $builder = ProcessBuilder::create([
            'platform',
            '--version',
        ]);
        $builder->setTimeout(null);
        $builder->enableOutput();
        $process = $builder->getProcess();
        if ($output->getVerbosity() >= $output::VERBOSITY_VERBOSE) {
            $output->writeln('<info>Checking platform.sh cli version</info>');
            $output->writeln($process->getCommandLine());
        }
        $function  = function ($type, $buffer) use ($output, $process) {
            $output->write($buffer);
        };
        $process->run($function);
        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Platform.sh cli is not installed.');
        }

        preg_match('!\d+\.\d+\.\d+!', $process->getOutput(), $matches);
        if (version_compare($matches[0], static::PLATFORM_VERSION, '<')) {
            throw new \RuntimeException('Platform.sh cli is not version '. static::PLATFORM_VERSION . ' or above. Run platform self-update.');
        }
    }
}
