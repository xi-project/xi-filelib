<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Platform;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityNotFoundException;
use PDOException;
use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\IdentityMap\Identifiable;

/**
 * Doctrine 2 backend for filelib
 *
 * @category Xi
 * @package  Filelib
 * @author   Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 * @author   pekkis
 */
class DoctrineOrmPlatform extends AbstractPlatform
{
    /**
     * File entity name
     *
     * @var string
     */
    private $fileEntityName = 'Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\File';

    /**
     * Folder entity name
     *
     * @var string
     */
    private $folderEntityName = 'Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\Folder';

    /**
     * Resource entity name
     *
     * @var string
     */
    private $resourceEntityName = 'Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\Resource';


    /**
     * Entity manager
     *
     * @var EntityManager
     */
    private $em;

    /**
     * @param  EventDispatcherInterface $eventDispatcher
     * @param  EntityManager    $em
     * @return DoctrineOrmPlatform
     */
    public function __construct(EntityManager $em)
    {
        $this->setEntityManager($em);
    }

    /**
     * Sets the fully qualified file entity classname
     *
     * @param string $fileEntityName
     */
    public function setFileEntityName($fileEntityName)
    {
        $this->fileEntityName = $fileEntityName;
    }

    /**
     * Returns the fully qualified file entity classname
     *
     * @return string
     */
    public function getFileEntityName()
    {
        return $this->fileEntityName;
    }

    /**
     * Sets the entity manager
     *
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Returns the entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Sets the fully qualified folder entity classname
     *
     * @param string $folderEntityName
     */
    public function setFolderEntityName($folderEntityName)
    {
        $this->folderEntityName = $folderEntityName;
    }

    /**
     * Returns the fully qualified folder entity classname
     *
     * @return string
     */
    public function getFolderEntityName()
    {
        return $this->folderEntityName;
    }

    /**
     * Sets the fully qualified resource entity classname
     *
     * @param string $resourceEntityName
     */
    public function setResourceEntityName($resourceEntityName)
    {
        $this->resourceEntityName = $resourceEntityName;
    }

    /**
     * Returns the fully qualified resource entity classname
     *
     * @return string
     */
    public function getResourceEntityName()
    {
        return $this->resourceEntityName;
    }


    /**
     * @see AbstractPlatform::doUpdateFile
     */
    public function updateFile(File $file)
    {
        $entity = $this->getFileReference($file);
        $entity->setFolder($this->getFolderReference($file->getFolderId()));
        $entity->setProfile($file->getProfile());
        $entity->setName($file->getName());
        $entity->setLink($file->getLink());
        $entity->setDateCreated($file->getDateCreated());
        $entity->setStatus($file->getStatus());
        $entity->setUuid($file->getUuid());
        $entity->setResource($this->em->getReference($this->getResourceEntityName(), $file->getResource()->getId()));
        $entity->setVersions($file->getVersions());

        $this->em->flush();

        return true;
    }

    /**
     * @see AbstractPlatform::doDeleteFile
     */
    public function deleteFile(File $file)
    {
        if (!$entity = $this->em->find($this->fileEntityName, $file->getId())) {
            return false;
        }

        $this->em->remove($entity);
        $this->em->flush();

        return true;
    }

    /**
     * @see AbstractPlatform::doCreateFolder
     */
    public function createFolder(Folder $folder)
    {
        $folderEntity = new $this->folderEntityName();
        $folderEntity->setParent($this->getFolderReference($folder->getParentId()));
        $folderEntity->setName($folder->getName());
        $folderEntity->setUrl($folder->getUrl());
        $folderEntity->setUuid($folder->getUuid());

        $this->em->persist($folderEntity);
        $this->em->flush();

        $folder->setId($folderEntity->getId());

        return $folder;
    }

    /**
     * @see AbstractPlatform::doUpdateFolder
     */
    public function updateFolder(Folder $folder)
    {
        try {
            $folderRow = $this->getFolderReference($folder->getId());

            if ($folder->getParentId()) {
                $folderRow->setParent($this->getFolderReference(
                    $folder->getParentId()
                ));
            } else {
                $folderRow->removeParent();
            }

            $folderRow->setName($folder->getName());
            $folderRow->setUrl($folder->getUrl());
            $folderRow->setUuid($folder->getUuid());

            $this->em->flush();

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @see AbstractPlatform::doUpdateResource
     */
    public function updateResource(Resource $resource)
    {
        try {
            $resourceRow = $this->em->getReference($this->getResourceEntityName(), $resource->getId());
            $resourceRow->setVersions($resource->getVersions());
            $resourceRow->setExclusive($resource->isExclusive());
            $resourceRow->setHash($resource->getHash());
            $this->em->flush();
            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @see AbstractPlatform::doDeleteFolder
     */
    public function deleteFolder(Folder $folder)
    {
        try {
            $folderEntity = $this->em->find($this->folderEntityName,
                                            $folder->getId());

            if (!$folderEntity) {
                return false;
            }

            $this->em->remove($folderEntity);
            $this->em->flush();

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @see AbstractPlatform::doDeleteResource
     */
    public function deleteResource(Resource $resource)
    {
        try {
            $entity = $this->em->find($this->resourceEntityName, $resource->getId());

            if (!$entity) {
                return false;
            }

            $this->em->remove($entity);
            $this->em->flush();

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @see AbstractPlatform::doCreateResource
     */
    public function createResource(Resource $resource)
    {
        $resourceRow = new $this->resourceEntityName();
        $resourceRow->setHash($resource->getHash());
        $resourceRow->setDateCreated($resource->getDateCreated());
        $resourceRow->setMimetype($resource->getMimetype());
        $resourceRow->setSize($resource->getSize());
        $resourceRow->setExclusive($resource->isExclusive());
        $this->em->persist($resourceRow);
        $this->em->flush();
        $resource->setId($resourceRow->getId());
        return $resource;
    }

    /**
     * @see AbstractPlatform::doUpload
     */
    public function createFile(File $file, Folder $folder)
    {
        $self = $this;

        return $this->em->transactional(function(EntityManager $em) use ($self, $file, $folder) {
            $fileEntityName = $self->getFileEntityName();

            $entity = new $fileEntityName;
            $entity->setFolder($self->getFolderReference($folder->getId()));
            $entity->setName($file->getName());
            $entity->setProfile($file->getProfile());
            $entity->setDateCreated($file->getDateCreated());
            $entity->setStatus($file->getStatus());
            $entity->setUuid($file->getUuid());
            $entity->setVersions($file->getVersions());

            $resource = $file->getResource();
            if ($resource) {
                $entity->setResource($em->getReference($self->getResourceEntityName(), $resource->getId()));
            }

            $em->persist($entity);

            try {
                $em->flush();
            } catch (PDOException $e) {
                $self->throwNonUniqueFileException($file, $folder);
            }

            $file->setId($entity->getId());
            $file->setFolderId($entity->getFolder()->getId());

            return $file;
        });
    }

    /**
     * @see AbstractPlatform::exportFolder
     */
    protected function exportFolder($folder)
    {
        return Folder::create(array(
            'id'        => $folder->getId(),
            'parent_id' => $folder->getParent() ? $folder->getParent()->getId() : null,
            'name'      => $folder->getName(),
            'url'       => $folder->getUrl(),
            'uuid'      => $folder->getUuid(),
        ));
    }

    /**
     * @see AbstractPlatform::exportFile
     */
    protected function exportFile($file)
    {
        return File::create(array(
            'id'            => $file->getId(),
            'folder_id'     => $file->getFolder() ? $file->getFolder()->getId() : null,
            'profile'       => $file->getProfile(),
            'name'          => $file->getName(),
            'link'          => $file->getLink(),
            'date_created' => $file->getDateCreated(),
            'status'        => $file->getStatus(),
            'uuid'          => $file->getUuid(),
            'resource' => ($file->getResource()) ? $this->exportResource($file->getResource()) : null,
            'versions' => $file->getVersions(),
        ));
    }

    /**
     * @see AbstractPlatform::exportResource
     */
    protected function exportResource($resource)
    {
        return Resource::create(array(
            'id' => $resource->getId(),
            'hash' => $resource->getHash(),
            'date_created' => $resource->getDateCreated(),
            'versions' => $resource->getVersions(),
            'mimetype' => $resource->getMimetype(),
            'size' => $resource->getSize(),
            'exclusive' => $resource->getExclusive(),
        ));
    }

    /**
     * @see AbstractPlatform::doGetNumberOfReferences
     */
    public function getNumberOfReferences(Resource $resource)
    {
        return $this->em->getConnection()->fetchColumn("SELECT COUNT(id) FROM xi_filelib_file WHERE resource_id = ?", array($resource->getId()));
    }

    /**
     * @param  File        $file
     * @return object|null
     */
    private function getFileReference(File $file)
    {
        return $this->em->getReference($this->fileEntityName, $file->getId());
    }

    /**
     * NOTE: Should be private!
     *
     * @param  integer     $id
     * @return object|null
     */
    public function getFolderReference($id)
    {
        return $this->em->getReference($this->folderEntityName, $id);
    }


    public function findByFinder(Finder $finder)
    {
        return false;
    }


    public function assertValidIdentifier(Identifiable $identifiable)
    {
        return is_numeric($identifiable->getId());
    }


    public function findByIds(array $ids, $className)
    {
        return false;
    }


}
