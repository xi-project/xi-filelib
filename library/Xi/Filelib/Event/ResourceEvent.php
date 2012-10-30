<?php

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\File\Resource;

class ResourceEvent extends Event
{
    /**
     * @var Resource
     */
    private $resource;
    
    public function __construct(Resource $resource)
    {
        $this->resource = $resource;
    }
    
    /**
     * Returns Resource
     * 
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
    
}
