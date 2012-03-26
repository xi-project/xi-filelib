<?php

namespace Xi\Bundle\FilelibBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="xi_filelib_folder")
 */
class Folder
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="foldername", type="string", length=255)
     */
    protected $name;
    
    /**
     * @ORM\Column(name="folderurl", type="string", length=5000)
     */
    protected $url;
    
    /**
     * @ORM\OneToMany(targetEntity="Folder", mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\ManyToOne(targetEntity="Folder", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parent;
    // ...
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string               $value
     * @return Folder
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
     * Set url
     *
     * @param  string               $value
     * @return Folder
     */
    public function setUrl($value)
    {
        $this->url = $value;
        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
    
    /**
     * Set parent
     *
     * @param  Folder $filelibFolder
     * @return Folder
     */
    public function setParent(Folder $filelibFolder)
    {
        $this->parent = $filelibFolder;
        return $this;
    }

    /**
     * Remove parent
     *
     * @return Folder
     */
    public function removeParent()
    {
        $this->parent = null;
        return $this;
    }

    /**
     * Get parent
     *
     * @return Folder|null
     */
    public function getParent()
    {
        return $this->parent;
    }
    

}
