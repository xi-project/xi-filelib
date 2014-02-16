<?php

namespace Xi\Filelib\Queue;

use Pekkis\Queue\Processor\MessageHandler;
use Pekkis\Queue\Message;
use Pekkis\Queue\Processor\Result;
use Pekkis\Queue\QueueInterface;
use Xi\Filelib\Command;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\File\Resource;

class FilelibMessageHandler implements MessageHandler
{
    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @var FileOperator
     */
    private $fiop;

    /**
     * @var FolderOperator
     */
    private $foop;

    public function willHandle(Message $message)
    {
        return ($message->getData() instanceof Command);
    }

    public function handle(Message $message, QueueInterface $queue)
    {
        $message->getData()->execute();
        return new Result(true);
    }
}
