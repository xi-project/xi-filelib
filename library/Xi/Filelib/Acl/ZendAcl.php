<?php

namespace Xi\Filelib\Acl;

use Zend_Acl;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;

/**
 * ZendAcl delegates access control to Zend ACL
 * 
 * @author pekkis
 *
 */
class ZendAcl implements Acl
{

    /**
     * @var \Zend_Acl
     */
    private $acl;
    
    /**
     * @var mixed
     */
    private $role;
    
    /**
     * @var mixed
     */
    private $anonymousRole;
    
    /**
     * Sets ACL
     * 
     * @param \Zend_Acl $acl
     */
    public function setAcl(\Zend_Acl $acl)
    {
        $this->acl = $acl;
        return $this;
    }

    /**
     * Returns ACL
     * 
     * @return \Zend_Acl
     */
    public function getAcl()
    {
        return $this->acl;
    }

    /**
     * Sets current role
     * 
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * Gets current role
     * 
     * @return mixed
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Sets anonymous role
     * 
     * @param mixed $anonymousRole
     */
    public function setAnonymousRole($anonymousRole)
    {
        $this->anonymousRole = $anonymousRole;
        return $this;
    }

    /**
     * Returns anonymous role
     * 
     * @return mixed
     */
    public function getAnonymousRole()
    {
        return $this->anonymousRole;
    }


    
    public function isFileReadable(File $file)
    {
        $resourceName = $this->getResourceIdentifier($file);
        return $this->getAcl()->isAllowed($this->getRole(), $resourceName, 'read');
    }

    public function isFileWritable(File $file)
    {
        $resourceName = $this->getResourceIdentifier($file);
        return $this->getAcl()->isAllowed($this->getRole(), $resourceName, 'write');
    }

    public function isFileReadableByAnonymous(File $file)
    {
        $resourceName = $this->getResourceIdentifier($file);
        return $this->getAcl()->isAllowed($this->getAnonymousRole(), $resourceName, 'read');
    }
    

    public function isFolderReadable(Folder $folder)
    {
        $resourceName = $this->getResourceIdentifier($folder);
        return $this->getAcl()->isAllowed($this->getRole(), $resourceName, 'read');
    }

    public function isFolderWritable(Folder $folder)
    {
        $resourceName = $this->getResourceIdentifier($folder);
        return $this->getAcl()->isAllowed($this->getRole(), $resourceName, 'write');
    }

    public function isFolderReadableByAnonymous(Folder $folder)
    {
        $resourceName = $this->getResourceIdentifier($folder);
        return $this->getAcl()->isAllowed($this->getAnonymousRole(), $resourceName, 'read');
    }
    
    public function getResourceIdentifier($resource)
    {
        if ($resource instanceof File) {
            return 'Xi_Filelib_File_' . $resource->getId();
        } elseif ($resource instanceof Folder) {
            return 'Xi_Filelib_Folder_' . $resource->getId();
        }
        
        $class = get_class($resource);
        throw new \InvalidArgumentException("Resource of class '{$class}' not identified");
        
    }
    
    
}