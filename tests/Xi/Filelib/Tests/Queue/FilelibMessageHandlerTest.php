<?php

namespace Xi\Filelib\Tests\Queue;

use Pekkis\Queue\Message;
use Xi\Filelib\Command\Command;
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
    public function willHandleMessagesContainingCommands()
    {
        $badMessage = Message::create('lus.tus', array('xoxoxo' => 'YOLO'));
        $this->assertFalse($this->handler->willHandle($badMessage));

        $goodMessage = Message::create(
            'lus.tus',
            $this->getMockedCommand()
        );

        $this->assertTrue($this->handler->willHandle($goodMessage));
    }

    /**
     * @test
     */
    public function commandsAreHandled()
    {
        $command = $this->getMockedCommand();
        $command->expects($this->once())->method('execute')->will($this->returnValue('xooxer'));

        $message = Message::create(
            'tenhunen.tenhustelee.ja.imee.banskua',
            $command
        );

        $ret = $this->handler->handle(
            $message,
            $this->getMockedQueue()
        );
        $this->assertInstanceOf('Pekkis\Queue\Processor\Result', $ret);
    }

}
