<?php

namespace Xi\Filelib\Backend\Doctrine2\Entity;

/**
 * @Entity
 * @Table(name="xi_filelib_folder")
 */
class Folder
{
    /**
     * @Column(name="id", type="integer")
     * @Id
     * @GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Column(name="foldername", type="string", length=255)
     */
    protected $name;
    
    /**
     * @Column(name="folderurl", type="string", length=5000)
     */
    protected $url;
    
    /**
     * @OneToOne(targetEntity="Folder")
     * @JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

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
