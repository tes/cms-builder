<?php

namespace tes\CmsBuilder\Command\EventListener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

class CommandTimer
{

    /**
     * @var \Symfony\Component\Stopwatch\Stopwatch $stopwatch
     */
    protected $stopwatch;

    /**
     * @var int
     */
    protected $startTime;

    /**
     * @var callable
     */
    protected $logger;

    /**
     * @var string
     *
     * @todo This can be made protected with PHP 7.1+.
     */
    const STOPWATCH_NAME = 'command';

    /**
     * CommandTimer constructor.
     *
     * @param callable $logger
     *   A callable to log the results; see CommandTimer::addListeners() for a
     *   complete description.
     */
    public function __construct(callable $logger)
    {
        $this->logger = $logger;
        $this->stopwatch = new Stopwatch();
    }

    /**
     * Start the command timer.
     */
    public function start() {
        $this->stopwatch->start(self::STOPWATCH_NAME);
        $this->startTime = time();
    }

    /**
     * Stop the command timer and report the results.
     */
    public function stop(ConsoleTerminateEvent $event) {
        $duration = $this->stopwatch->stop(self::STOPWATCH_NAME)->getDuration();
        $command = $event->getCommand();
        $record = [
          'time' => date('c', $this->startTime),
          'name' => $command->getName(),
          'duration' => $duration,
          'version' => $command->getApplication()->getVersion(),
        ];

        // As of PHP 7.0 the following is possible, but it's a shame to limit
        // cms-builder to 7 just to time commands!
        // ($this->logger)($record);
        call_user_func($this->logger, $record);
    }

    /**
     * Attach console command event listeners to time commands.
     *
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
     *   The dispatcher to add the listeners to
     * @param callable $logger
     *   A callable that accepts the command time data as an associative array,
     *   keyed by:
     *   - time: human-readable date and time the command was started;
     *   - name: the name of the command;
     *   - duration: the duration of the command in ms;
     *   - version: the application version.
     */
    public static function addListeners(EventDispatcherInterface $dispatcher, callable $logger) {
        $timer = new static($logger);
        $dispatcher->addListener(ConsoleEvents::COMMAND, [$timer, 'start']);
        $dispatcher->addListener(ConsoleEvents::TERMINATE, [$timer, 'stop']);
    }

}