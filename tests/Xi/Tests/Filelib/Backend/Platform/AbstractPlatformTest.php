<?php

namespace Xi\Tests\Filelib\Backend\Platform;

class AbstractPlatformTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function getEventDispatcherShouldReturnEventDispatcher()
    {
        $ed = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');

        $backend = $this->getMockBuilder('Xi\Filelib\Backend\Platform\AbstractPlatform')
            ->setMethods(array())
            ->setConstructorArgs(array($ed))
            ->getMockForAbstractClass();

        $this->assertSame($ed, $backend->getEventDispatcher());
    }
}
