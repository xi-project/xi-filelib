<?php

namespace Xi\Filelib\Backend\Doctrine2\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTime;

/**
 * @ORM\Entity
 * @ORM\Table(name="xi_filelib_resource")
 */
class Resource
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="hash", type="string", length=255, nullable=false)
     */
    private $hash;

    /**
     * @ORM\Column(name="mimetype", type="string", length=255, nullable=false)
     */
    private $mimetype;

    /**
     * @ORM\Column(name="filesize", type="integer", nullable=false)
     */
    private $size;

    /**
     * @ORM\Column(name="exclusive", type="boolean", nullable=false)
     */
    private $exclusive;

    /**
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="resource")
     **/
    private $files;

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
     * Set hash
     *
     * @param  string             $hash
     * @return Resource
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
        return $this;
    }

    /**
     * Get hash
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
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
     * Returns exclusive
     *
     * @return boolean
     */
    public function getExclusive()
    {
        return $this->exclusive;
    }

    /**
     * Sets exclusive
     *
     * @param boolean $exclusive
     */
    public function setExclusive($exclusive)
    {
        $this->exclusive = $exclusive;
        return $this;
    }

    /**
     * Returns date created
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
     * @return Resource
     */
    public function setDateCreated(DateTime $dateCreated)
    {
        $this->dateCreated = $dateCreated;
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
