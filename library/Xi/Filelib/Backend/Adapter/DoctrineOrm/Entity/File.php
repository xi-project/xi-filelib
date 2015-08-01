<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="xi_filelib_file",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="folderid_filename_unique",columns={"folder_id","filename"})}
 * )
 */
class File extends BaseEntity
{

    /**
     * @ORM\Column(name="fileprofile", type="string", length=255)
     */
    private $profile;

    /**
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity="Folder")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id", nullable=false)
     */
    private $folder;

    /**
     * @ORM\ManyToOne(targetEntity="Resource", inversedBy="files", fetch="EAGER")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id", nullable=false)
     **/
    private $resource;


    /**
     * Set profile
     *
     * @param  string $value
     * @return File
     */
    public function setProfile($value)
    {
        $this->profile = $value;

        return $this;
    }

    /**
     * Get profile
     *
     * @return string
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * Set name
     *
     * @param  string $value
     * @return File
     */
    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set filelib folder
     *
     * @param  Folder $folder
     * @return File
     */
    public function setFolder(Folder $folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get emerald filelib folder
     *
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Returns date uploaded
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * Sets date uploaded
     *
     * @param  DateTime $dateUploaded
     * @return File
     */
    public function setDateCreated(DateTime $dateUploaded)
    {
        $this->dateCreated = $dateUploaded;

        return $this;
    }

    /**
     * Returns status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets status
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
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
