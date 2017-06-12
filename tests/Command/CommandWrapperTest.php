<?php

namespace tes\CmsBuilder\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use tes\CmsBuilder\Command\CommandWrapper;

class CommandWrapperTest extends TestCase
{

    /**
     * Don't call configure on the wrapped command when constructing the
     * wrapper.
     *
     * I know it's often cosidered received wisdom not to test private/protected
     * methods - you should be verifying the class contract. In this case I feel
     * that the wrapper's class contract includes not calling configure on the
     * wrapped command.
     */
    public function testConfigure() {
        $mock = $this->getConfiguredMockBuilder(Command::class)
          ->setMethods(['configure', 'getName'])
          ->getMock();
        $mock->method('getName')
            ->willReturn('mock_command');
        $mock->expects($this->never())
            ->method('configure');

        new CommandWrapper($mock);
    }

    /**
     * Returns a mock builder configured to match TestCase::createMock().
     *
     * @param string $originalClassName
     *   The class to return the double for.
     *
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     */
    protected function getConfiguredMockBuilder($originalClassName) {
        return $this->getMockBuilder($originalClassName)
          ->disableOriginalConstructor()
          ->disableOriginalClone()
          ->disableArgumentCloning()
          ->disallowMockingUnknownTypes();
    }

}