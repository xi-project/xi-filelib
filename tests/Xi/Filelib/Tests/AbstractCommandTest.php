<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\AbstractCommand;

class AbstractCommandTest extends \Xi\Filelib\Tests\TestCase
{

    private $command;

    public function setUp()
    {
        $this->command = $this->getMockBuilder('Xi\Filelib\AbstractCommand')
            ->setMethods(array('execute'))
            ->setConstructorArgs(array())
            ->getMockForAbstractClass();
    }

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\AbstractCommand');
        $this->assertImplements('Xi\Filelib\Command', 'Xi\Filelib\AbstractCommand');
    }

    /**
     * @test
     */
    public function outputShouldDefaultToNullOutput()
    {
        $this->assertInstanceOf('Symfony\Component\Console\Output\NullOutput', $this->command->getOutput());
    }

    /**
     * @test
     */
    public function outputShouldBeSettable()
    {
        $output = $this->getMock('Symfony\Component\Console\Output\OutputInterface');
        $ret = $this->command->setOutput($output);
        $this->assertSame($this->command, $ret);
        $this->assertSame($output, $this->command->getOutput());
    }
}
