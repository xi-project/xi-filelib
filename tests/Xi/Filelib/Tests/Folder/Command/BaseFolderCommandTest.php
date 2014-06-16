<?php

namespace Xi\Filelib\Tests\Folder\Command;

class BaseFolderCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Folder\Command\BaseFolderCommand');
        $this->assertImplements(
            'Xi\Filelib\Command\Command',
            'Xi\Filelib\Folder\Command\BaseFolderCommand'
        );
    }

    /**
     * @test
     */
    public function attachesFromFilelib()
    {
        $foop = $this->getMockedFolderRepository();
        $filelib = $this->getMockedFilelib(null, null, $foop);

        $command = $this->getMockForAbstractClass('Xi\Filelib\Folder\Command\BaseFolderCommand');
        $command->attachTo($filelib);

        $this->assertAttributeSame($foop, 'folderRepository', $command);


    }


}
