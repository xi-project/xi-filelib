<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Authorization\Adapter;

use Xi\Filelib\Authorization\AuthorizationAdapter;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderOperator;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Acl\Model\AclProviderInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;

class SymfonyAuthorizationAdapter implements AuthorizationAdapter
{
    /**
     * @var FolderOperator
     */
    private $folderOperator;

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

    public function __construct(
        SecurityContextInterface $context,
        AclProviderInterface $aclProvider,
        $folderBased = true
    ) {

        $this->context = $context;
        $this->aclProvider = $aclProvider;
        $this->folderBased = $folderBased;
    }

    public function attachTo(FileLibrary $filelib)
    {
        $this->folderOperator = $filelib->getFolderOperator();
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
     * Returns whether file is writable
     *
     * @param  File    $file
     * @return boolean
     */
    public function isFileWritable(File $file)
    {
        if ($this->isFolderBased()) {
            return $this->isFolderWritable($this->getFilesFolder($file));
        }

        return $this->context->isGranted('EDIT', $file);
    }

    /**
     * Returns whether file is readable
     *
     * @param  File    $file
     * @return boolean
     */
    public function isFileReadable(File $file)
    {
        if ($this->isFolderBased()) {
            return $this->isFolderReadable($this->getFilesFolder($file));
        }

        return $this->context->isGranted('VIEW', $file);
    }

    /**
     * Returns whether file is readable by anonymous
     *
     * @param  File    $file
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
     * @param  Folder  $file
     * @return boolean
     */
    public function isFolderWritable(Folder $folder)
    {
        return $this->context->isGranted('EDIT', $folder);
    }

    /**
     * Returns whether folder is readable
     *
     * @param  Folder  $file
     * @return boolean
     */
    public function isFolderReadable(Folder $folder)
    {
        return $this->context->isGranted('VIEW', $folder);
    }

    /**
     * Returns whether folder is readable by anonymous
     *
     * @param  Folder  $file
     * @return boolean
     */
    public function isFolderReadableByAnonymous(Folder $folder)
    {
        return $this->anonymousAclQueryWith($folder);
    }

    /**
     * Queries ACL with domain object
     *
     * @param object $domainObject
     *
     * @return boolean
     */
    public function anonymousAclQueryWith($domainObject)
    {
        $oid = ObjectIdentity::fromDomainObject($domainObject);
        try {
            $acl = $this->aclProvider->findAcl($oid);
            $roleIdentity = new RoleSecurityIdentity('IS_AUTHENTICATED_ANONYMOUSLY');

            return $acl->isGranted(array(MaskBuilder::MASK_VIEW), array($roleIdentity), false);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Returns a file's folder
     *
     * @param  File   $file
     * @return Folder
     */
    private function getFilesFolder(File $file)
    {
        return $this->folderOperator->find($file->getFolderId());
    }
}
