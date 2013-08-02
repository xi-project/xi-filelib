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
        $command = $this->getMockBuilder('Xi\Filelib\AbstractCommand')
                        ->setMethods(array('execute'))
                        ->setConstructorArgs(array())
                        ->getMockForAbstractClass();

        $uuid = $command->getUuid();
        $this->assertUuid($uuid);

        $this->assertSame($uuid, $command->getEnqueueReturnValue());


    }

}
