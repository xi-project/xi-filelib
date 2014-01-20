<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue\Processor;

use Xi\Filelib\Queue\Message;

interface MessageHandler
{
    /**
     * @param Message $message
     * @return bool
     */
    public function willHandle(Message $message);

    /**
     * @param Message $message
     * @return Result
     */
    public function handle(Message $message);
}
