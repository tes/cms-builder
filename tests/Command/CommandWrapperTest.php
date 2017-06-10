<?php

namespace tes\CmsBuilder\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use tes\CmsBuilder\Command\CommandWrapper;

class CommandWrapperTest extends TestCase
{

    public function setUp() {
        $mock = $this->createMock(Command::class);
        $mock->method('getAliases')
          ->willReturn(['old1', 'old2']);
        $mock->method('getName')
          ->willReturn('mock_command');
        $this->mockCommand = $mock;
    }

    public function testNoAliases() {
        $command = new CommandWrapper($this->mockCommand);
        $this->assertEmpty($command->getAliases());
    }

    public function testTwoAliasesFromConstructor() {
        $command = new CommandWrapper($this->mockCommand, ['new1', 'new2']);
        $this->assertEquals(['new1', 'new2'], $command->getAliases());
    }

    /** @var  \Symfony\Component\Console\Command\Command $mockCommand */
    protected $mockCommand;

}