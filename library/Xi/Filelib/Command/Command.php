<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Command;

use Xi\Filelib\Attacher;

interface Command extends Attacher
{
    public function execute();

    /**
     * @return string
     */
    public function getTopic();
}
