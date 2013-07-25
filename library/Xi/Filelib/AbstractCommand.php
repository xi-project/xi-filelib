<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Rhumsaa\Uuid\Uuid;

abstract class AbstractCommand implements Command
{
    /**
     * @var string
     */
    protected $uuid;

    /**
     * @param string $uuid
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getEnqueueReturnValue()
    {
        return $this->getUuid();
    }
}
