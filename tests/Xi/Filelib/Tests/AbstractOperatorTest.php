<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\EnqueueableCommand;
use Xi\Filelib\AbstractOperator;

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

        $op->setCommandStrategy('lussenhof', EnqueueableCommand::STRATEGY_ASYNCHRONOUS);

    }

    /**
     *
     * @test
     */
    public function generateUuidShouldGenerateUuid()
    {
        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->disableOriginalConstructor()
                         ->getMockForAbstractClass();

        $uuid = $op->generateUuid();
        $this->assertRegExp("/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/", $uuid);
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

        $command = $this->getMockBuilder('Xi\Filelib\EnqueueableCommand')
                        ->disableOriginalConstructor()
                        ->getMock();

        $queue = $this->getMock('Xi\Filelib\Queue\Queue');

        $op->expects($this->once())->method('getCommandStrategy')
           ->with($this->equalTo('tussi'))
           ->will($this->returnValue(EnqueueableCommand::STRATEGY_ASYNCHRONOUS));

        $op->expects($this->any())->method('getQueue')
           ->will($this->returnValue($queue));

        $queue->expects($this->once())->method('enqueue')
              ->with($this->isInstanceOf('Xi\Filelib\Command'))
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

        $command = $this->getMockBuilder('Xi\Filelib\EnqueueableCommand')
                        ->disableOriginalConstructor()
                        ->getMock();

        $queue = $this->getMock('Xi\Filelib\Queue\Queue');

        $op->expects($this->once())->method('getCommandStrategy')
           ->with($this->equalTo('tussi'))
           ->will($this->returnValue(EnqueueableCommand::STRATEGY_SYNCHRONOUS));

        $op->expects($this->any())->method('getQueue')
           ->will($this->returnValue($queue));

        $queue->expects($this->never())->method('enqueue');

        $command->expects($this->once())->method('execute')
                ->will($this->returnValue('executed!!!'));

        $ret = $op->executeOrQueue($command, 'tussi', array());

        $this->assertEquals('executed!!!', $ret);

    }

    public function provideCallbackStrategies()
    {
        return array(
            array('asynchronous', EnqueueableCommand::STRATEGY_ASYNCHRONOUS),
            array('synchronous', EnqueueableCommand::STRATEGY_SYNCHRONOUS),
        );
    }

    /**
     * @test
     * @dataProvider provideCallbackStrategies
     */
    public function executeOrQueueShouldUtilizeCallbacks($expectedValue, $strategy)
    {
        $callbacks = array(
            EnqueueableCommand::STRATEGY_ASYNCHRONOUS => function(AbstractOperator $op, $ret) {
                return 'asynchronous';
            },
            EnqueueableCommand::STRATEGY_SYNCHRONOUS => function(AbstractOperator $op, $ret) {
                return 'synchronous';
            }
        );

        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
            ->disableOriginalConstructor()
            ->setMethods(array('getCommandStrategy', 'getQueue'))
            ->getMock();

        $command = $this->getMockBuilder('Xi\Filelib\EnqueueableCommand')
            ->disableOriginalConstructor()
            ->getMock();

        $queue = $this->getMock('Xi\Filelib\Queue\Queue');

        $op->expects($this->once())->method('getCommandStrategy')
            ->with($this->equalTo('tussi'))
            ->will($this->returnValue($strategy));

        $op->expects($this->any())->method('getQueue')
            ->will($this->returnValue($queue));

        $command->expects($this->any())->method('execute')
            ->will($this->returnValue('originalValue'));

        $queue->expects($this->any())->method('enqueue')
            ->will($this->returnValue('originalValue'));

        $ret = $op->executeOrQueue($command, 'tussi', $callbacks);

        $this->assertEquals($expectedValue, $ret);

    }

    /**
     * @test
     */
    public function createCommandCreatesCommandObject()
    {
        $mockClass = $this
            ->getMockClass(
                'Xi\Filelib\File\Command\AbstractFileCommand',
                array('execute', 'serialize', 'unserialize')
            );

        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $fileop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();

        $command = $op->createCommand(
            $mockClass,
            array($fileop)
        );

        $this->assertInstanceOf($mockClass, $command);
    }

}
