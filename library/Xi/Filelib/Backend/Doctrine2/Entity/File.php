<?php

namespace Xi\Filelib\Backend\Doctrine2\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="xi_filelib_file",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="folderid_filename_unique",columns={"folder_id","filename"})}
 * )
 */
class File
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="fileprofile", type="string", length=255)
     */
    private $profile;

    /**
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="filelink", type="string", length=255, nullable=true, unique=true)
     */
    private $link;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @ORM\Column(name="uuid", type="string", length=36, nullable=false, unique=true)
     */
    private $uuid;

    /**
     * @ORM\ManyToOne(targetEntity="Folder")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id", nullable=false)
     */
    private $folder;

    /**
     * @ORM\ManyToOne(targetEntity="Resource", inversedBy="files")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id", nullable=false)
     **/
    private $resource;

    /**
     * @ORM\Column(name="versions", type="array")
     */
    private $versions = array();

    /**
     * Get id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set profile
     *
     * @param  string             $value
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
     * @param  string             $value
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
     * Set link
     *
     * @param  string             $value
     * @return File
     */
    public function setLink($value)
    {
        $this->link = $value;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
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
     * @param DateTime $dateUploaded
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
     * Sets uuid
     *
     * @param string $uuid
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * Returns uuid
     *
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     *
     * @return Resource
     */
    public function getResource()
    {
        return $this->resource;
    }


    public function setResource(Resource $resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     *
     * @param array $versions
     */
    public function setVersions(array $versions)
    {
        $this->versions = $versions;
        return $this;
    }

    public function getVersions()
    {
        return $this->versions;
    }



}
