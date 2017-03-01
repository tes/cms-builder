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
 * Removes aliases from commands provided by dependencies.
 */
class CommandWrapper extends Command
{
    /**
     * @var Command
     */
    protected $command;

    /**
     * @var array
     */
    protected $aliases;
    
    public function __construct(Command $command, $aliases = [])
    {
        $this->command = $command;
        parent::__construct($command->getName());
        $this->aliases = $aliases;
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
        $this->command->configure();
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
        return $this->command->setCode($code);
    }

    public function mergeApplicationDefinition($mergeArgs = true)
    {
        return $this->command->mergeApplicationDefinition($mergeArgs);
    }

    public function setDefinition($definition)
    {
        return $this->command->setDefinition($definition);
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
        return $this->command->addArgument($name, $mode, $description, $default);
    }

    public function addOption($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        return $this->command->addOption($name, $shortcut, $mode, $description, $default);
    }

    public function setName($name)
    {
        parent::setName($name);
        return $this->command->setName($name);
    }

    public function setProcessTitle($title)
    {
        return $this->command->setProcessTitle($title);
    }

    public function getName()
    {
        return $this->command->getName();
    }

    public function setDescription($description)
    {
        return $this->command->setDescription($description);
    }

    public function getDescription()
    {
        return $this->command->getDescription();
    }

    public function setHelp($help)
    {
        return $this->command->setHelp($help);
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
        return $this->command->setAliases($aliases);
    }

    public function getAliases()
    {
        return $this->aliases;
    }

    public function getSynopsis($short = false)
    {
        return $this->command->getSynopsis($short);
    }

    public function addUsage($usage)
    {
        return $this->command->addUsage($usage);
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