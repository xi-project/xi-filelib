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
        $uuid = 'tussi-id';

        $fileOperator = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();

        $fileOperator->expects($this->once())->method('generateUuid')
                     ->will($this->returnValue($uuid));

        $command = $this->getMockBuilder('Xi\Filelib\File\Command\AbstractFileCommand')
                        ->setMethods(array('execute'))
                        ->setConstructorArgs(array($fileOperator))
                        ->getMockForAbstractClass();

        $this->assertSame($fileOperator, $command->getFileOperator());

        $this->assertSame($uuid, $command->getUuid());


    }



}

