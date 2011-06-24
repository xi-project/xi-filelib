<?php

namespace Xi\Filelib\Backend\Doctrine2\Entity;

/**
 * @Entity
 * @Table(name="emerald_filelib_folder")
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
     * Set parent
     *
     * @param  Folder $emeraldFilelibFolder
     * @return Folder
     */
    public function setParent(Folder $emeraldFilelibFolder)
    {
        $this->parent = $emeraldFilelibFolder;

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
