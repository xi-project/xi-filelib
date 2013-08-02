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
        $command = $this->getMockBuilder('Xi\Filelib\Folder\Command\AbstractFolderCommand')
                        ->setMethods(array('execute'))
                        ->setConstructorArgs(array())
                        ->getMockForAbstractClass();

        $this->assertUuid($command->getUuid());

        $this->assertNull($command->getFolderOperator());

        $foop = $this->getMockedFolderOperator();
        $filelib = $this->getMockedFilelib(null, null, $foop);

        $command->attachTo($filelib);

        $this->assertSame($foop, $command->getFolderOperator());
    }

}
