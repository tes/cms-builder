<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 22/02/2017
 * Time: 14:37
 */

namespace tes\CmsBuilder\Command;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Base class that wraps a Symfony command.
 *
 * It would be neater to implement an interface than extend the base class, but
 * there isn't an interface sadly/strangely.
 */
class CommandWrapper extends Command
{
    /**
     * @var Command
     */
    protected $command;

    public function __construct(Command $command)
    {
        parent::__construct();
        $this->command = $command;
    }

    public function ignoreValidationErrors()
    {
        $this->command->ignoreValidationErrors();
    }

    public function setApplication(Application $application = null)
    {
        $this->command->setApplication($application);
    }

    public function setHelperSet(HelperSet $helperSet)
    {
        $this->command->setHelperSet($helperSet);
    }

    public function getHelperSet()
    {
        return $this->command->getHelperSet();
    }

    public function getApplication()
    {
        return $this->command->getApplication();
    }

    public function isEnabled()
    {
        return $this->command->isEnabled();
    }

    protected function configure()
    {
        // It shouldn't matter what name we set on the wrapper,
        parent::setName('command_wrapper');
        // I suspect the only call to ::configure() will come from the
        // constructor, in which case we need never proxy the call to the
        // wrapped object. (The wrapper takes a constructed command which should
        // already be configured.) However in an abundance of caution, all calls
        // to ::configure() are proxied after the call from the constructor.
        if (!empty($this->command)) {
            $this->command->configure();
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->command->execute($input, $output);
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $this->command->interact($input, $output);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->command->initialize($input, $output);
    }

    public function run(InputInterface $input, OutputInterface $output)
    {
        return $this->command->run($input, $output);
    }

    public function setCode($code)
    {
        $this->command->setCode($code);
        return $this;
    }

    public function mergeApplicationDefinition($mergeArgs = true)
    {
        $this->command->mergeApplicationDefinition($mergeArgs);
    }

    public function setDefinition($definition)
    {
        $this->command->setDefinition($definition);
        return $this;
    }

    public function getDefinition()
    {
        return $this->command->getDefinition();
    }

    public function getNativeDefinition()
    {
        return $this->command->getNativeDefinition();
    }

    public function addArgument($name, $mode = null, $description = '', $default = null)
    {
        $this->command->addArgument($name, $mode, $description, $default);
        return $this;
    }

    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        $this->command->addOption($name, $shortcut, $mode, $description, $default);
        return $this;
    }

    public function setName($name)
    {
        $this->command->setName($name);
        // Does calling parent::setName() make any practical difference?
        return parent::setName($name);
    }

    public function setProcessTitle($title)
    {
        $this->command->setProcessTitle($title);
        return $this;
    }

    public function getName()
    {
        return $this->command->getName();
    }

    public function setDescription($description)
    {
        $this->command->setDescription($description);
        return $this;
    }

    public function getDescription()
    {
        return $this->command->getDescription();
    }

    public function setHelp($help)
    {
        $this->command->setHelp($help);
        return $this;
    }

    public function getHelp()
    {
        return $this->command->getHelp();
    }

    public function getProcessedHelp()
    {
        return $this->command->getProcessedHelp();
    }

    public function setAliases($aliases)
    {
        $this->command->setAliases($aliases);
        return $this;
    }

    public function getAliases()
    {
        return $this->command->getAliases();
    }

    public function getSynopsis($short = false)
    {
        return $this->command->getSynopsis($short);
    }

    public function addUsage($usage)
    {
        $this->command->addUsage($usage);
        return $this;
    }

    public function getUsages()
    {
        return $this->command->getUsages();
    }

    public function getHelper($name)
    {
        return $this->command->getHelper($name);
    }

    public function asText()
    {
        return $this->command->asText();
    }

    public function asXml($asDom = false)
    {
        return $this->command->asXml($asDom);
    }

}