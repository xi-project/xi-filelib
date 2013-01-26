<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue;

use InvalidArgumentException;

/**
 * Filelib queue message
 */
class Message
{
    private $identifier;

    private $body;

    public function __construct($body)
    {
        if (!is_string($body)) {
            throw new InvalidArgumentException("Message body must be a string");
        }

        $this->body = $body;
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getBody()
    {
        return $this->body;
    }

}
