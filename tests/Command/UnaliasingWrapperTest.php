<?php

namespace tes\CmsBuilder\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use tes\CmsBuilder\Command\UnaliasingWrapper;

class UnaliasingWrapperTest extends TestCase
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
        $command = new UnaliasingWrapper($this->mockCommand);
        $this->assertEmpty($command->getAliases());
    }

    public function testTwoAliasesFromConstructor() {
        $command = new UnaliasingWrapper($this->mockCommand, ['new1', 'new2']);
        $this->assertEquals(['new1', 'new2'], $command->getAliases());
    }

    /** @var  \Symfony\Component\Console\Command\Command $mockCommand */
    protected $mockCommand;

}