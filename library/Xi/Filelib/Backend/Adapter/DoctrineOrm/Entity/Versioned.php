<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Xi\Filelib\Resource\ConcreteResource;

/**
 * @ORM\Entity
 * @ORM\Table(name="xi_filelib_versioned")
 */
class Versioned
{
    /**
     * @ORM\Column(name="uuid", type="string", length=255)
     * @ORM\Id
     * @var string
     */
    private $uuid;

    /**
     * @ORM\Column(name="version", type="string", length=255)
     * @ORM\Id
     * @var string
     */
    private $version;

    /**
     * @ORM\ManyToOne(targetEntity="Resource", inversedBy="versioneds", fetch="EAGER")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id", nullable=false)
     * @var ConcreteResource
     **/
    private $resource;

    public function __construct($uuid, $version)
    {
        $this->uuid = $uuid;
        $this->version = $version;
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
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Returns resource
     *
     * @return ConcreteResource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param  ConcreteResource $resource
     * @return File
     */
    public function setResource(ConcreteResource $resource)
    {
        $this->resource = $resource;

        return $this;
    }
}
