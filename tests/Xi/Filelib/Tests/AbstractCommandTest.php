<?php

namespace Xi\Filelib\Tests;

use Xi\Filelib\AbstractCommand;

class AbstractCommandTest extends \Xi\Filelib\Tests\TestCase
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
