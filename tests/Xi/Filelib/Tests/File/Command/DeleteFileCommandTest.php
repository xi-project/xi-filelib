<?php

namespace Xi\Filelib\Tests\File\Command;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\File\Command\DeleteFileCommand;
use Xi\Filelib\Events;
use Xi\Filelib\Resource\ResourceRepository;

class DeleteFileCommandTest extends \Xi\Filelib\Tests\TestCase
{

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\Command\DeleteFileCommand'));
        $this->assertContains('Xi\Filelib\Command\Command', class_implements('Xi\Filelib\File\Command\DeleteFileCommand'));
    }

    /**
     * @return array
     */
    public function provideForDeleteDelegation()
    {
        return array(
            array(false),
            array(true),
        );
    }

    /**
     * @test
     * @dataProvider provideForDeleteDelegation
     */
    public function deleteShouldDelegateCorrectly($exclusiveResource)
    {
        $ed = $this->getMockedEventDispatcher();

        $ed
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::FILE_BEFORE_DELETE),
                $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
            );

        $ed
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(
                $this->equalTo(Events::FILE_AFTER_DELETE),
                $this->isInstanceOf('Xi\Filelib\Event\FileEvent')
            );

        $op = $this->getMockedFileRepository();

        $file = File::create(array('id' => 1, 'profile' => 'lussen', 'resource' => Resource::create(array('exclusive' => $exclusiveResource))));

        $backend = $this->getMockedBackend();
        $backend
            ->expects($this->once())
            ->method('deleteFile')
            ->with($this->equalTo($file));

        $storage = $this->getMockedStorage();

        $rere = $this->getMockedResourceRepository();

        if ($exclusiveResource) {
            $rere
                ->expects($this->once())
                ->method('createCommand')
                ->with(
                    ResourceRepository::COMMAND_DELETE,
                    $this->isType('array')
                )
                ->will($this->returnValue($this->getMockedCommand('topic', true)));
        } else {
            $rere
                ->expects($this->never())
                ->method('createCommand');
        }


        $filelib = $this->getMockedFilelib(
            null,
            array(
                'fire' => $op,
                'storage' => $storage,
                'ed' => $ed,
                'backend' => $backend,
                'rere' => $rere,
            )
        );

        $command = new DeleteFileCommand($file);
        $command->attachTo($filelib);
        $command->execute();
    }

    /**
     * @test
     */
    public function topicIsCorrect()
    {
        $command = $this->getMockBuilder('Xi\Filelib\File\Command\DeleteFileCommand')
            ->disableOriginalConstructor()
            ->setMethods(array('execute'))
            ->getMock();

        $this->assertEquals('xi_filelib.command.file.delete', $command->getTopic());
    }
}
