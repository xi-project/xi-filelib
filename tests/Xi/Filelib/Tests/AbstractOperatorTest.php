<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\AbstractOperator;
use Xi\Filelib\Command\ExecutionStrategy\ExecutionStrategy;

class AbstractOperatorTest extends TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\AbstractOperator'));
    }

    /**
     * @test
     */
    public function getBackendShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getBackend');

        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $operator->getBackend();

    }

    /**
     * @test
     */
    public function getStorageShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getStorage');

        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $operator->getStorage();

    }

    /**
     * @test
     */
    public function getEventDispatcherShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getEventDispatcher');

        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $operator->getEventDispatcher();
    }

    /**
     * @test
     */
    public function getQueueShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getQueue');

        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $operator->getQueue();
    }

    /**
     * @test
     */
    public function getFilelibShouldReturnFilelib()
    {
        $filelib = $this->getMockedFilelib();

        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $this->assertSame($filelib, $operator->getFilelib());

    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function gettingInvalidCommandShouldThrowException()
    {
        $filelib = $this->getMockedFilelib();
        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $op->getCommandStrategy('lussenhof');

    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function settingInvalidCommandShouldThrowException()
    {
        $filelib = $this->getMockedFilelib();

        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $op->setCommandStrategy('lussenhof', ExecutionStrategy::STRATEGY_ASYNCHRONOUS);

    }

    /**
     * @test
     */
    public function executeOrQueueShouldEnqueueWithAsynchronousStrategy()
    {

        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                   ->disableOriginalConstructor()
                   ->setMethods(array('getCommandStrategy', 'getQueue'))
                   ->getMock();

        $command = $this->getMockedCommand('tenhunen.imaisee.mehevaa');
        $queue = $this->getMockedQueue();

        $op->expects($this->once())->method('getCommandStrategy')
           ->with($this->equalTo('tussi'))
           ->will($this->returnValue(ExecutionStrategy::STRATEGY_ASYNCHRONOUS));

        $op->expects($this->any())->method('getQueue')
           ->will($this->returnValue($queue));

        $queue->expects($this->once())->method('enqueue')
              ->with($this->isType('string'), $this->isInstanceOf('Xi\Filelib\Command\Command'))
              ->will($this->returnValue('tussi-id'));

        $ret = $op->executeOrQueue($command, 'tussi', array());

        $this->assertEquals('tussi-id', $ret);

    }

    /**
     * @test
     */
    public function executeOrQueueShouldExecuteWithSynchronousStrategy()
    {

        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                   ->disableOriginalConstructor()
                   ->setMethods(array('getCommandStrategy', 'getQueue'))
                   ->getMock();

        $command = $this->getMockedCommand();

        $queue = $this->getMockedQueue();

        $op->expects($this->once())->method('getCommandStrategy')
           ->with($this->equalTo('tussi'))
           ->will($this->returnValue(ExecutionStrategy::STRATEGY_SYNCHRONOUS));

        $op->expects($this->any())->method('getQueue')
           ->will($this->returnValue($queue));

        $queue->expects($this->never())->method('enqueue');

        $command->expects($this->once())->method('execute')
                ->will($this->returnValue('executed!!!'));

        $ret = $op->executeOrQueue($command, 'tussi', array());

        $this->assertEquals('executed!!!', $ret);

    }

    /**
     * @test
     */
    public function createCommandCreatesCommandObject()
    {
        $filelib = $this->getMockedFilelib();

        $mockClass = $this
            ->getMockClass(
                'Xi\Filelib\File\Command\AbstractFileCommand',
                array('execute', 'serialize', 'unserialize', 'attachTo', 'getMessage', 'getTopic')
            );

        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
            ->setConstructorArgs(array($filelib))
            ->getMockForAbstractClass();

        $command = $op->createCommand(
            $mockClass,
            array()
        );

        $this->assertInstanceOf($mockClass, $command);
    }
}
