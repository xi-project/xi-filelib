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

    public function attachesFromFilelib()
    {
        $foop = $this->getMockedFolderOperator();
        $filelib = $this->getMockedFilelib(null, null, $foop);

        $command = $this->getMockForAbstractClass('Xi\Filelib\Command\AbstractFolderCommand');
        $command->attachTo($filelib);

        $this->assertAttributeSame($foop, 'folderOperator', $command);


    }


}
