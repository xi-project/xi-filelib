<?php

namespace Xi\Filelib\Tests\Folder\Command;

class AbstractFolderCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\Folder\Command\AbstractFolderCommand');
        $this->assertImplements(
            'Xi\Filelib\Command\Command',
            'Xi\Filelib\Folder\Command\AbstractFolderCommand'
        );
    }

    /**
     * @test
     */
    public function attachesFromFilelib()
    {
        $foop = $this->getMockedFolderOperator();
        $filelib = $this->getMockedFilelib(null, null, $foop);

        $command = $this->getMockForAbstractClass('Xi\Filelib\Folder\Command\AbstractFolderCommand');
        $command->attachTo($filelib);

        $this->assertAttributeSame($foop, 'folderOperator', $command);


    }


}
