<?php

namespace Xi\Filelib\Queue;

use Pekkis\Queue\Processor\MessageHandler;
use Pekkis\Queue\Message;
use Pekkis\Queue\Processor\Result;
use Pekkis\Queue\QueueInterface;
use Xi\Filelib\Command\Command;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderRepository;
use Xi\Filelib\File\Resource;

class FilelibMessageHandler implements MessageHandler
{
    public function willHandle(Message $message)
    {
        return ($message->getData() instanceof Command);
    }

    public function handle(Message $message, QueueInterface $queue)
    {
        $command = $message->getData();
        if ($command instanceof UuidReceiver) {
            $command->setUuid($message->getUuid());
        }
        $command->execute();
        return new Result(true);
    }
}
