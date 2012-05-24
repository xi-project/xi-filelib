<?php

namespace Xi\Tests\Filelib;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Command;

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
    public function getPublisherShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getPublisher');

        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $operator->getPublisher();

    }


    /**
     * @test
     */
    public function getAclShouldDelegateToFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->once())->method('getAcl');

        $operator = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $operator->getAcl();
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
     * @return FileLibrary
     */
    private function getMockedFilelib()
    {
        $mock = $this->getMock('Xi\Filelib\FileLibrary');
        return $mock;
    }


    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function gettingInvalidCommandShouldThrowException()
    {
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
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
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                         ->setMethods(array())
                         ->setConstructorArgs(array($filelib))
                         ->getMockForAbstractClass();

        $op->setCommandStrategy('lussenhof', Command::STRATEGY_ASYNCHRONOUS);

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
        // executeOrQueue(Command $commandObj, $commandName, array $callbacks = array())

        $op = $this->getMockBuilder('Xi\Filelib\AbstractOperator')
                   ->disableOriginalConstructor()
                   ->setMethods(array('getCommandStrategy', 'getQueue'))
                   ->getMock();

        $command = $this->getMockBuilder('Xi\Filelib\Command')
                        ->disableOriginalConstructor()
                        ->getMock();

        $queue = $this->getMock('Xi\Filelib\Queue\Queue');

        $op->expects($this->once())->method('getCommandStrategy')
           ->with($this->equalTo('tussi'))
           ->will($this->returnValue(COMMAND::STRATEGY_ASYNCHRONOUS));

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

        $command = $this->getMockBuilder('Xi\Filelib\Command')
                        ->disableOriginalConstructor()
                        ->getMock();



        $queue = $this->getMock('Xi\Filelib\Queue\Queue');

        $op->expects($this->once())->method('getCommandStrategy')
           ->with($this->equalTo('tussi'))
           ->will($this->returnValue(COMMAND::STRATEGY_SYNCHRONOUS));

        $op->expects($this->any())->method('getQueue')
           ->will($this->returnValue($queue));

        $queue->expects($this->never())->method('enqueue');

        $command->expects($this->once())->method('execute')
                ->will($this->returnValue('executed!!!'));

        $ret = $op->executeOrQueue($command, 'tussi', array());

        $this->assertEquals('executed!!!', $ret);

    }



}