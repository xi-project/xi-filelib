<?php

namespace Xi\Filelib\Tests\File\Command;

class BaseFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertClassExists('Xi\Filelib\File\Command\BaseFileCommand');
        $this->assertImplements(
            'Xi\Filelib\Command\Command',
            'Xi\Filelib\File\Command\BaseFileCommand'
        );
    }

    /**
     * @test
     */
    public function attachesFromFilelib()
    {
        $fiop = $this->getMockedFileRepository();
        $filelib = $this->getMockedFilelib(null, $fiop);

        $command = $this->getMockForAbstractClass('Xi\Filelib\File\Command\BaseFileCommand');
        $command->attachTo($filelib);

        $this->assertAttributeSame($fiop, 'fileRepository', $command);
    }
}
