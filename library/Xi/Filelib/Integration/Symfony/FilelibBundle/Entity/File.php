<?php

namespace Xi\Filelib\Integration\Symfony\FilelibBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="xi_filelib_file",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="folderid_filename_unique",columns={"folder_id","filename"})}
 * )
 */
class File
{
    /**
     * Xi filelib
     *
     * @var \Xi_Filelib
     */
    private $_filelib;

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
     * Set mimetype
     *
     * @param  string             $value
     * @return XiFilelibFile
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
     * @return XiFilelibFile
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
     * @return XiFilelibFile
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
     * @return XiFilelibFile
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
     * @return XiFilelibFile
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
     * @return XiFilelibFolder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    
    
    
    
    public function getDateUploaded()
    {
        return $this->date_uploaded;
    }
    
    
    public function setDateUploaded(\DateTime $dateUploaded)
    {
        $this->date_uploaded = $dateUploaded;
    }
    
    
    
}
