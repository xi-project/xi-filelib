<?php

namespace Xi\Filelib\Acl;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class SymfonyAcl implements Acl
{

    /**
     * @var FileLibrary
     */
    private $filelib;
    
    /**
     * @var SecurityContextInterface
     */
    private $context;
    
    /**
     *
     * @var AclProviderInterface
     */
    private $aclProvider;
    
    
    /**
     * Defines whether the ACL is strictly folder based.
     * If true, delegates all file queries to their folders.
     * 
     * @var boolean
     */
    private $folderBased;
            
    public function __construct(FileLibrary $filelib, SecurityContextInterface $context, AclProviderInterface $aclProvider, $folderBased = true)
    {
        $this->filelib = $filelib;
        $this->context = $context;
        $this->aclProvider = $aclProvider;
        $this->folderBased = $folderBased;
    }
    
    /**
     * Returns whether ACL is folder based
     * 
     * @return boolean
     */
    public function isFolderBased()
    {
        return $this->folderBased;
    }
    
    
    /**
     * Returns filelib
     * 
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->filelib;
    }
    
    /**
     * Returns security context
     * 
     * @return SecurityContext
     */
    public function getContext()
    {
        return $this->context;
    }
    
    
    public function getAclProvider()
    {
        return $this->aclProvider;
    }
    
    
    /**
     * Returns whether file is writable
     * 
     * @param File $file
     * @return boolean 
     */
    public function isFileWritable(File $file)
    {
        if ($this->isFolderBased()) {
            return $this->isFolderWritable($this->getFilesFolder($file));
        }
        return $this->getContext()->isGranted('EDIT', $file);
    }
    
    
    /**
     * Returns whether file is readable
     * 
     * @param File $file
     * @return boolean 
     */
    public function isFileReadable(File $file)
    {
        if ($this->isFolderBased()) {
            return $this->isFolderReadable($this->getFilesFolder($file));
        }
        return $this->getContext()->isGranted('VIEW', $file);
    }
    
    /**
     * Returns whether file is readable by anonymous
     * 
     * @param File $file
     * @return boolean 
     */
    public function isFileReadableByAnonymous(File $file)
    {
        if ($this->isFolderBased()) {
            return $this->isFolderReadableByAnonymous($this->getFilesFolder($file));
        }
        return $this->anonymousAclQueryWith($file);
    }
    
    /**
     * Returns whether folder is writable
     * 
     * @param Folder $file
     * @return boolean 
     */
    public function isFolderWritable(Folder $folder)
    {
        return $this->getContext()->isGranted('EDIT', $folder);
    }
    
    
    /**
     * Returns whether folder is readable
     * 
     * @param Folder $file
     * @return boolean 
     */
    public function isFolderReadable(Folder $folder)
    {
        return $this->getContext()->isGranted('VIEW', $folder);
    }
    
    /**
     * Returns whether folder is readable by anonymous
     * 
     * @param Folder $file
     * @return boolean 
     */
    public function isFolderReadableByAnonymous(Folder $folder)
    {
        return $this->anonymousAclQueryWith($folder);
    }
    
    /**
     * Queries ACL with domain object
     * 
     * @param type $domainObject
     * 
     * @return boolean
     */
    public function anonymousAclQueryWith($domainObject)
    {
        $oid = ObjectIdentity::fromDomainObject($domainObject);
        try {
            $acl = $this->getAclProvider()->findAcl($oid);
            $roleIdentity = new RoleSecurityIdentity('IS_AUTHENTICATED_ANONYMOUSLY');
            return $acl->isGranted(array(MaskBuilder::MASK_VIEW), array($roleIdentity), false);
        } catch (\Exception $e) {
            return false;
        }
    }
    
    
 
    /**
     * Returns file's folder
     * 
     * @param File $file
     * @return Folder
     */
    private function getFilesFolder(File $file)
    {
        return $this->getFilelib()->getFolderOperator()->find($file->getFolderId());
    }
    
    
    
    
    
    
    
    
}
