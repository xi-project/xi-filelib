<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony\Queue;

use Pekkis\Queue\Message;
use Pekkis\Queue\Processor\MessageHandler;
use Pekkis\Queue\Processor\Result;
use Pekkis\Queue\QueueInterface;
use Xi\Filelib\Asynchrony\Serializer\SerializedCallback;

class FilelibMessageHandler implements MessageHandler
{
    public function willHandle(Message $message)
    {
        return ($message->getData() instanceof SerializedCallback);
    }

    public function handle(Message $message, QueueInterface $queue)
    {
        /** @var SerializedCallback $callback */
        $callback = $message->getData();

        call_user_func_array($callback->callback, $callback->params);

        return new Result(true);
    }
}
