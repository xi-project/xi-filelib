<?php

namespace Xi\Tests\Filelib;

use Xi\Filelib\AbstractCommand;

class AbstractCommandTest extends \Xi\Tests\Filelib\TestCase
{

    /**
     * @test
     */
    public function interfaceShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\AbstractCommand'));
        $this->assertContains('Xi\Filelib\Command', class_implements('Xi\Filelib\AbstractCommand'));
    }

    /**
     * @test
     */
    public function classShouldInitializeCorrectly()
    {
        $uuid = 'tussen-hof';

        $command = $this->getMockBuilder('Xi\Filelib\AbstractCommand')
                        ->setMethods(array('execute'))
                        ->setConstructorArgs(array($uuid))
                        ->getMockForAbstractClass();

        $this->assertEquals($uuid, $command->getEnqueueReturnValue());
        $this->assertEquals($uuid, $command->getUuid());

    }


}

