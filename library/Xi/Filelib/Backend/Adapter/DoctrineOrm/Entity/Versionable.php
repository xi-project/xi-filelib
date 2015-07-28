<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="xi_filelib_versionable")
 */
class Versionable
{
    /**
     * @ORM\Column(name="uuid", type="string", length=255)
     * @ORM\Id
     */
    private $uuid;

    /**
     * @ORM\Column(name="version", type="string", length=255)
     * @ORM\Id
     */
    private $version;

    /**
     * @ORM\ManyToOne(targetEntity="Resource", inversedBy="versionables", fetch="EAGER")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id", nullable=false)
     **/
    private $resource;

    public function __construct($uuid, $version)
    {
        $this->uuid = $uuid;
        $this->version = $version;
    }

    /**
     * Returns resource
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param  Resource $resource
     * @return File
     */
    public function setResource(Resource $resource)
    {
        $this->resource = $resource;

        return $this;
    }
}
