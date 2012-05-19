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
    protected $id;

    /**
     * @ORM\Column(name="mimetype", type="string", length=255)
     */
    protected $mimetype;

    /**
     * @ORM\Column(name="fileprofile", type="string", length=255)
     */
    protected $profile;

    /**
     * @ORM\Column(name="filesize", type="integer", nullable=true)
     */
    protected $size;

    /**
     * @ORM\Column(name="filename", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="filelink", type="string", length=255, nullable=true, unique=true)
     */
    protected $link;

    /**
     * @ORM\Column(name="date_uploaded", type="datetime")
     */
    protected $date_uploaded;

    /**
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="Folder")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id", nullable=false)
     */
    protected $folder;

    /**
     * @ORM\ManyToOne(targetEntity="Resource", inversedBy="files")
     * @ORM\JoinColumn(name="resource_id", referencedColumnName="id", nullable=true)
     **/
    private $resource;

    /**
     * Get id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set mimetype
     *
     * @param  string             $value
     * @return File
     */
    public function setMimetype($value)
    {
        $this->mimetype = $value;

        return $this;
    }

    /**
     * Get mimetype
     *
     * @return string
     */
    public function getMimetype()
    {
        return $this->mimetype;
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
     * Set size
     *
     * @param  integer            $value
     * @return File
     */
    public function setSize($value)
    {
        $this->size = $value;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
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
    public function getDateUploaded()
    {
        return $this->date_uploaded;
    }


    /**
     * Sets date uploaded
     *
     * @param DateTime $dateUploaded
     * @return File
     */
    public function setDateUploaded(DateTime $dateUploaded)
    {
        $this->date_uploaded = $dateUploaded;
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


}
