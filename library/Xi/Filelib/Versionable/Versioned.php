<?php

namespace Xi\Filelib\Versionable;

use Xi\Filelib\Resource\ConcreteResource;

class Versioned
{
    /**
     * @var Version
     */
    private $version;

    /**
     * @var Resource
     */
    private $resource;

    public function __construct($version, ConcreteResource $resource)
    {
        $this->version = Version::get($version);
        $this->resource = $resource;
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return \Xi\Filelib\Resource\Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}