<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue;



interface Enqueueable
{
    /**
     * @return Message
     */
    public function getMessage();
}
