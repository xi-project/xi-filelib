<?php

namespace Xi\Filelib\Acl;

use \Zend_Acl,
    Xi\Filelib\Folder\Folder,
    Xi\Filelib\File\File
    ;

/**
 * Zend ACL for Filelib
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class ZendAcl implements Acl
{

    /**
     * @var \Zend_Acl
     */
    private $_acl;
    
    /**
     * @var mixed
     */
    private $_role;
    
    /**
     * @var mixed
     */
    private $_anonymousRole;
    
    /**
     * Sets ACL
     * 
     * @param \Zend_Acl $acl
     */
    public function setAcl(\Zend_Acl $acl)
    {
        $this->_acl = $acl;
    }

    /**
     * Returns ACL
     * 
     * @return \Zend_Acl
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Sets current role
     * 
     * @param mixed $role
     */
    public function setRole($role)
    {
        $this->_role = $role;
    }

    /**
     * Gets current role
     * 
     * @return mixed
     */
    public function getRole()
    {
        return $this->_role;
    }

    /**
     * Sets anonymous role
     * 
     * @param mixed $anonymousRole
     */
    public function setAnonymousRole($anonymousRole)
    {
        $this->_anonymousRole = $anonymousRole;
    }

    /**
     * Returns anonymous role
     * 
     * @return mixed
     */
    public function getAnonymousRole()
    {
        return $this->_anonymousRole;
    }

    public function isReadable($resource)
    {
        $resourceName = $this->getResourceIdentifier($resource);
        
        return $this->getAcl()->isAllowed($this->getRole(), $resourceName, 'read');
    }

    public function isWriteable($resource)
    {
        $resourceName = $this->getResourceIdentifier($resource);
        
        return $this->getAcl()->isAllowed($this->getRole(), $resourceName, 'write');
    }

    public function isReadableByAnonymous($resource)
    {
        $resourceName = $this->getResourceIdentifier($resource);
        
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