<?php

namespace tes\CmsBuilder\Command;

use Symfony\Component\Console\Command\Command;

/**
 * Removes aliases from commands provided by dependencies.
 *
 * @todo I think it would make sense for this to set the local aliases on
 *   setAliases() rather than proxy the call to the wrapped command.
 */

class UnaliasingWrapper extends CommandWrapper
{

    public function __construct(Command $command, $aliases = [])
    {
        $this->aliases = $aliases;
        parent::__construct($command);
    }

    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * @var string[]
     */
    protected $aliases;

}