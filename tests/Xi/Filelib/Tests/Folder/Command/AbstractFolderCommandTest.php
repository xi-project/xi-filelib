<?php

namespace Xi\Filelib\Tests\Folder\Command;

class AbstractFolderCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\Command\AbstractFolderCommand'));
        $this->assertContains('Xi\Filelib\Folder\Command\FolderCommand', class_implements('Xi\Filelib\Folder\Command\AbstractFolderCommand'));
    }

    /**
     * @test
     */
    public function commandShouldInitializeProperly()
    {
        $uuid = 'loso-id';

        $folderOperator = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')->disableOriginalConstructor()->getMock();

        $folderOperator->expects($this->once())->method('generateUuid')
                       ->will($this->returnValue($uuid));

        $command = $this->getMockBuilder('Xi\Filelib\Folder\Command\AbstractFolderCommand')
                        ->setMethods(array('execute'))
                        ->setConstructorArgs(array($folderOperator))
                        ->getMockForAbstractClass();

        $this->assertSame($folderOperator, $command->getFolderOperator());
        $this->assertSame($uuid, $command->getUuid());

    }

}
