<?php

namespace tes\CmsBuilder\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use tes\CmsBuilder\Config;

/**
 * A Symfony Console command decorator that logs the command's execution time.
 */
class TimedWrapper extends CommandWrapper
{

    public function __construct(Command $command) {
        parent::__construct($command);
        $this->stopwatch = new Stopwatch();
        $home = getenv('HOME');
        $this->logDirectory = $home ? "$home/.cms-builder" : "/tmp/cms-builder-logs";
        $this->logFile = 'commands.log';
    }

    /**
     * Run the wrapped command and log the time taken.
     */
    public function run(InputInterface $input, OutputInterface $output)
    {
        $start = time();
        $this->stopwatch->start('run');
        $ret = parent::run($input, $output);
        $duration = $this->stopwatch->stop('run')->getDuration();
        $this->logTime($start, $duration);
        return $ret;
    }

    /**
     * Log the time a command took to run.
     *
     * @param int $time
     *   The unix timestamp when the command was initiated.
     * @param int $duration
     *   The time taken in ms
     */
    protected function logTime($time, $duration) {
        if (!file_exists($this->logDirectory)) {
            mkdir($this->logDirectory);
        }
        if (!is_dir($this->logDirectory)) {
            // For some reason there's a file in place of ~/.cms-builder: abort.
            return;
        }

        $record = [
            'time' => date('c', $time),  // time the command started
            'name' => $this->getName(),         // command name
            'duration' => $duration,            // time taken in ms
            'version' => $this->getApplication()->getVersion(), // cms-builder version
            'site' => Config::getSite(),        // site being built (where relevant)
            'site' => Config::getSite(),        // site being built (where relevant)
        ];

        $file = fopen("$this->logDirectory/$this->logFile", 'a');
        fputcsv($file, $record);
    }

    /** @var  string $logDirectory */
    protected $logDirectory;

    /** @var   */
    protected $logFile;

    /** @var \Symfony\Component\Stopwatch\Stopwatch $stopwatch */
    protected $stopwatch;
}