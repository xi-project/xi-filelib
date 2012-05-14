<?php

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
