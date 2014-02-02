<?php

namespace Xi\Filelib\Tests\Queue;

use Pekkis\Queue\Message;
use Xi\Filelib\Command;
use Xi\Filelib\EnqueueableCommand;
use Xi\Filelib\File\Command\AfterUploadFileCommand;
use Xi\Filelib\File\Command\CopyFileCommand;
use Xi\Filelib\File\Command\DeleteFileCommand;
use Xi\Filelib\File\Command\UpdateFileCommand;
use Xi\Filelib\File\Command\UploadFileCommand;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\Folder\Command\CreateByUrlFolderCommand;
use Xi\Filelib\Folder\Command\CreateFolderCommand;
use Xi\Filelib\Folder\Command\DeleteFolderCommand;
use Xi\Filelib\Folder\Command\UpdateFolderCommand;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Queue\FilelibMessageHandler;

class FilelibMessageHandlerTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var FilelibMessageHandler
     */
    private $handler;

    public function setUp()
    {
        $this->handler = new FilelibMessageHandler();

    }

    /**
     * @test
     */
    public function willHandleAllRelevantCommands()
    {
        $commands = array(
            'xi_filelib.command.file.after_upload',
            'xi_filelib.command.file.copy',
            'xi_filelib.command.file.delete',
            'xi_filelib.command.file.update',
            'xi_filelib.command.file.upload',
            'xi_filelib.command.folder.create_by_url',
            'xi_filelib.command.folder.create',
            'xi_filelib.command.folder.delete',
            'xi_filelib.command.folder.update',
        );

        foreach ($commands as $command) {
            $msg = Message::create($command);
            $this->assertTrue($this->handler->willHandle($msg));
        }
    }

    /**
     * @test
     */
    public function wontHandleUnknownCommands()
    {
        $msg = Message::create('puuppa.de.la.pöksy');
        $this->assertFalse($this->handler->willHandle($msg));
    }


    public function provideCommands()
    {
        $file = File::create(
            array('id' => 12345, 'resource' => Resource::create(array('id' => 987)))
        );
        $folder = Folder::create(array('id' => 12345));

        return array(
            array(new DeleteFolderCommand($folder)),
            array(new CreateByUrlFolderCommand('lusso/grande')),
            array(new CreateFolderCommand($folder)),
            array(new UpdateFolderCommand($folder)),
            array(new DeleteFileCommand($file)),
            array(new UploadFileCommand(new FileUpload(ROOT_TESTS. '/data/self-lussing-manatee.jpg'), $folder, 'luss')),
            array(new UpdateFileCommand($file)),
            array(new CopyFileCommand($file, $folder)),
            array(new AfterUploadFileCommand($file)),
            array(new AfterUploadFileCommand($file), true),
        );
    }


    /**
     * @test
     * @dataProvider provideCommands
     */
    public function commandsAreHandled(EnqueueableCommand $command, $throwUp = false)
    {
        $file = File::create(
            array('id' => 12345, 'resource' => Resource::create(array('id' => 987)))
        );
        $folder = Folder::create(array('id' => 54321));

        $expectedClass = get_class($command);

        $self = $this;
        $commandBuilder = function ($class, $args) use ($self, $expectedClass, $throwUp)  {
            $self->assertEquals($class, $expectedClass);
            $mock = $this->getMockBuilder($class)->setMethods(array('execute'))->setConstructorArgs($args)->getMock();

            if ($throwUp) {
                $mock->expects($this->once())
                    ->method('execute')
                    ->will($this->throwException(new \Exception('Game over man, game over!')));
            } else {
                $mock->expects($this->once())
                    ->method('execute')
                    ->will($this->returnValue(true));
            }
            return $mock;
        };

        $fiop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->disableOriginalConstructor()
            ->setMethods(array('createCommand', 'find'))
            ->getMock();
        $fiop->expects($this->any())->method('createCommand')->will($this->returnCallback($commandBuilder));
        $fiop->expects($this->any())->method('find')->will($this->returnValue($file));

        $foop = $this->getMockBuilder('Xi\Filelib\Folder\FolderOperator')
            ->disableOriginalConstructor()
            ->setMethods(array('createCommand', 'find'))
            ->getMock();
        $foop->expects($this->any())->method('createCommand')->will($this->returnCallback($commandBuilder));
        $foop->expects($this->any())->method('find')->will($this->returnValue($folder));

        $filelib = $this->getMockedFilelib(null, $fiop, $foop);
        $this->handler->attachTo($filelib);

        $ret = $this->handler->handle($command->getMessage());
        $this->assertInstanceOf('Pekkis\Queue\Processor\Result', $ret);

        if ($throwUp) {
            $this->assertFalse($ret->isSuccess());
        } else {
            $this->assertTrue($ret->isSuccess());
        }

    }

}
