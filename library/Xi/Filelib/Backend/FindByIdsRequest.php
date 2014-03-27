<?php

namespace Xi\Filelib\Backend;

use ArrayIterator;

class FindByIdsRequest
{
    private $notFoundIds;

    private $foundIds;

    private $foundObjects = array();

    public function __construct($ids, $className)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $this->notFoundIds = $ids;
        $this->foundIds = array();
        $this->className = $className;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->foundObjects);
    }

    public function getNotFoundIds()
    {
        return $this->notFoundIds;
    }

    public function getClassName()
    {
        return $this->className;
    }
}
