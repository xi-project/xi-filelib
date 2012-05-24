<?php

namespace Xi\Filelib;

abstract class AbstractCommand implements Command
{

    protected $uuid;

    public function __construct($uuid)
    {
        $this->uuid = $uuid;
    }


    public function getUuid()
    {
        return $this->uuid;
    }


    public function getEnqueueReturnValue()
    {
        return $this->getUuid();
    }

}
