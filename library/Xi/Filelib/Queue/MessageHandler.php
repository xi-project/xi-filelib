<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue;

interface MessageHandler
{
    /**
     * @param Message $message
     * @return bool
     */
    public function willHandle(Message $message);

    /**
     * @param Message $message
     * @return ProcessorResult
     */
    public function handle(Message $message);
}
