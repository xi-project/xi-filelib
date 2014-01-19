<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue;

use Rhumsaa\Uuid\Uuid;

class Message implements Enqueueable
{
    private $type;

    private $uuid;

    private $data = array();

    private $internal = array();

    private function __construct($uuid, $type, $data = array())
    {
        $this->uuid = $uuid;
        $this->type = $type;
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getUuid()
    {
        return $this->uuid;
    }

    public function setInternal($key, $value)
    {
        $this->internal[$key] = $value;
        return $this;
    }

    public function getInternal($key, $default = null)
    {
        if (!isset($this->internal[$key])) {
            return $default;
        }
        return $this->internal[$key];
    }

    public function getMessage()
    {
        return $this;
    }

    public function getIdentifier()
    {
        return $this->getInternal('identifier');
    }

    public function setIdentifier($identifier)
    {
        return $this->setInternal('identifier', $identifier);
    }


    public static function create($type, $data = array())
    {
        return new self(Uuid::uuid4()->toString(), $type, $data);
    }

    public function toArray()
    {
        return array(
            'uuid' => $this->uuid,
            'type' => $this->type,
            'data' => $this->data
        );
    }

    public static function fromArray($arr)
    {
        return new self($arr['uuid'], $arr['type'], $arr['data']);
    }
}
