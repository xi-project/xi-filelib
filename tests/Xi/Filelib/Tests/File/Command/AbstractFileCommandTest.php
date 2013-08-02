<?php

namespace Xi\Filelib\Tests\File\Command;

class AbstractFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\AbstractFileCommand'));
        $this->assertContains('Xi\Filelib\File\Command\FileCommand', class_implements('Xi\Filelib\File\Command\AbstractFileCommand'));
    }

    /**
     * @test
     */
    public function commandShouldInitializeProperly()
    {
        $command = $this->getMockBuilder('Xi\Filelib\File\Command\AbstractFileCommand')
                        ->setMethods(array('execute'))
                        ->setConstructorArgs(array())
                        ->getMockForAbstractClass();

        $this->assertSame(null, $command->getFileOperator());
        $this->assertUuid($command->getUuid());

    }

}
