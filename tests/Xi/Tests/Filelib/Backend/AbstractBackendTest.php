<?php

class AbstractBackendTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @test
     */
    public function getEventDispatcherShouldReturnEventDispatcher()
    {
        $ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\AbstractBackend')
            ->setMethods(array())
            ->setConstructorArgs(array($ed))
            ->getMockForAbstractClass();

        $this->assertSame($ed, $backend->getEventDispatcher());
    }


}
