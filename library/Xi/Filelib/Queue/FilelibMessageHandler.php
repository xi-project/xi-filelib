<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue;

use Pekkis\Queue\Message;
use Pekkis\Queue\Processor\MessageHandler;
use Pekkis\Queue\Processor\Result;
use Pekkis\Queue\QueueInterface;
use Xi\Filelib\Command\Command;

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
