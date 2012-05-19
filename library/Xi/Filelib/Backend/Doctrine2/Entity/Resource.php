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
    protected $id;

    /**
     * @ORM\Column(name="hash", type="string", length=255)
     */
    protected $hash;

    /**
     * @ORM\Column(name="date_created", type="datetime")
     */
    protected $date_created;

    /**
     * @ORM\OneToMany(targetEntity="File", mappedBy="resource")
     **/
    private $files;

    /**
     * @ORM\ManyToOne(targetEntity="Folder")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id", nullable=false)
     */
    protected $folder;

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
    public function setMimetype($hash)
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
     * Returns date created
     *
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }


    /**
     * Sets date uploaded
     *
     * @param DateTime $dateUploaded
     * @return Resource
     */
    public function setDateCreated(DateTime $dateUploaded)
    {
        $this->date_created = $dateCreated;
        return $this;
    }

}
