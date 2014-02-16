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

    public function attachesFromFilelib()
    {
        $fiop = $this->getMockedFileOperator();
        $filelib = $this->getMockedFilelib(null, $fiop);

        $command = $this->getMockForAbstractClass('Xi\Filelib\Command\AbstractFileCommand');
        $command->attachTo($filelib);

        $this->assertAttributeSame($fiop, 'fileOperator', $command);


    }
}
