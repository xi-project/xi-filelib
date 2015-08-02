<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter;

use ArrayIterator;
use Doctrine\Common\Util\Debug;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Iterator;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Resource\ConcreteResource;
use Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity\Versioned;
use Xi\Filelib\Versionable\Versionable;

use Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity\File as FileEntity;
use Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity\Resource as ResourceEntity;
use Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity\Folder as FolderEntity;


/**
 * Doctrine 2 backend for filelib
 *
 * @category Xi
 * @package  Filelib
 * @author   Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 * @author   pekkis
 */
class DoctrineOrmBackendAdapter extends BaseDoctrineBackendAdapter implements BackendAdapter
{
    /**
     * @var EntityManager
     */
    private $em;

    private $map = [
        ConcreteResource::class => ResourceEntity::class,
        File::class => FileEntity::class,
        Folder::class => FolderEntity::class
    ];

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @see BackendAdapter::updateFile
     */
    public function updateFile(File $file)
    {
        $entity = $this->em->getReference(FileEntity::class, $file->getId());
        $entity->setFolder($this->em->getReference(FolderEntity::class, $file->getFolderId()));
        $entity->setProfile($file->getProfile());
        $entity->setName($file->getName());
        $entity->setDateCreated($file->getDateCreated());
        $entity->setStatus($file->getStatus());
        $entity->setUuid($file->getUuid());
        $entity->setResource($this->em->getReference(ResourceEntity::class, $file->getResource()->getId()));
        $entity->setData($file->getData()->toArray());

        $this->em->flush($entity);
        return true;
    }

    /**
     * @see BackendAdapter::deleteFile
     */
    public function deleteFile(File $file)
    {
        if (!$entity = $this->em->find(FileEntity::class, $file->getId())) {
            return false;
        }

        $this->em->remove($entity);
        $this->em->flush($entity);

        return true;
    }

    /**
     * @see BackendAdapter::createFolder
     */
    public function createFolder(Folder $folder)
    {
        $folderEntity = new FolderEntity();

        if ($folder->getParentId()) {
            $folderEntity->setParent($this->em->getReference(FolderEntity::class, $folder->getParentId()));
        }

        $folderEntity->setName($folder->getName());
        $folderEntity->setUrl($folder->getUrl());
        $folderEntity->setUuid($folder->getUuid());
        $folderEntity->setData($folder->getData()->toArray());

        $this->em->persist($folderEntity);
        $this->em->flush($folderEntity);

        $folder->setId($folderEntity->getId());

        return $folder;
    }

    /**
     * @see BackendAdapter::updateFolder
     */
    public function updateFolder(Folder $folder)
    {
        try {
            $folderRow = $this->em->getReference(
                FolderEntity::class,
                $folder->getId()
            );

            if ($folder->getParentId()) {
                $folderRow->setParent(
                    $this->em->getReference(FolderEntity::class, $folder->getParentId())
                );
            } else {
                $folderRow->removeParent();
            }

            $folderRow->setName($folder->getName());
            $folderRow->setUrl($folder->getUrl());
            $folderRow->setUuid($folder->getUuid());
            $folderRow->setData($folder->getData()->toArray());

            $this->em->flush($folderRow);

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @see BackendAdapter::updateResource
     */
    public function updateResource(ConcreteResource $resource)
    {
        try {
            $resourceRow = $this->em->getReference(ResourceEntity::class, $resource->getId());
            $resourceRow->setUuid($resource->getUuid());
            $resourceRow->setData($resource->getData()->toArray());
            $resourceRow->setHash($resource->getHash());
            $this->em->flush($resourceRow);

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @see BackendAdapter::deleteFolder
     */
    public function deleteFolder(Folder $folder)
    {
        try {
            $folderEntity = $this->em->find(FolderEntity::class, $folder->getId());

            if (!$folderEntity) {
                return false;
            }

            $this->em->remove($folderEntity);
            $this->em->flush($folderEntity);

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @see BackendAdapter::deleteResource
     */
    public function deleteResource(ConcreteResource $resource)
    {
        try {
            $entity = $this->em->find(ResourceEntity::class, $resource->getId());

            if (!$entity) {
                return false;
            }

            $this->em->remove($entity);
            $this->em->flush($entity);

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @see BackendAdapter::createResource
     */
    public function createResource(ConcreteResource $resource)
    {
        $resourceRow = new ResourceEntity();
        $resourceRow->setUuid($resource->getUuid());
        $resourceRow->setHash($resource->getHash());
        $resourceRow->setDateCreated($resource->getDateCreated());
        $resourceRow->setMimetype($resource->getMimetype());
        $resourceRow->setSize($resource->getSize());
        $this->em->persist($resourceRow);
        $this->em->flush($resourceRow);
        $resource->setId($resourceRow->getId());

        return $resource;
    }

    /**
     * @see BackendAdapter::createFile
     */
    public function createFile(File $file, Folder $folder)
    {
        return $this->em->transactional(
            function (EntityManager $em) use ($file, $folder) {

                $entity = new FileEntity();

                $entity->setFolder($this->em->getReference(FolderEntity::class, $folder->getId()));
                $entity->setName($file->getName());
                $entity->setProfile($file->getProfile());
                $entity->setDateCreated($file->getDateCreated());
                $entity->setStatus($file->getStatus());
                $entity->setUuid($file->getUuid());
                $entity->setData($file->getData()->toArray());

                $resource = $file->getResource();
                if ($resource) {
                    $entity->setResource($em->getReference(ResourceEntity::class, $resource->getId()));
                }

                $em->persist($entity);
                $em->flush($entity);

                $file->setId($entity->getId());
                $file->setFolderId($entity->getFolder()->getId());

                return $file;
            }
        );
    }

    /**
     * @see BackendAdapter::getNumberOfReferences
     */
    public function getNumberOfReferences(ConcreteResource $resource)
    {
        return $this->em
            ->getConnection()
            ->fetchColumn(
                "SELECT COUNT(id) FROM xi_filelib_file WHERE resource_id = ?",
                array(
                    $resource->getId()
                )
            );
    }

    /**
     * @see BackendAdapter::findByIds
     */
    public function findByIds(FindByIdsRequest $request)
    {
        if ($request->isFulfilled()) {
            return $request;
        }

        $ids = $request->getNotFoundIds();
        $className = $request->getClassName();

        $resources = $this->classNameToResources[$className];

        $repo = $this->em->getRepository($this->map[$className]);
        $rows = $repo->findBy(
            array(
                'id' => $ids
            )
        );

        $rows = new ArrayIterator($rows);
        return $request->foundMany($this->$resources['exporter']($rows));
    }

    /**
     * @param  Iterator      $iter
     * @return ArrayIterator
     */
    protected function exportFolders(Iterator $iter)
    {
        $ret = new ArrayIterator(array());
        foreach ($iter as $folder) {
            $ret->append(
                Folder::create(
                    array(
                        'id' => $folder->getId(),
                        'parent_id' => $folder->getParent() ? $folder->getParent()->getId() : null,
                        'name' => $folder->getName(),
                        'url' => $folder->getUrl(),
                        'uuid' => $folder->getUuid(),
                        'data' => $folder->getData(),
                    )
                )
            );
        }

        return $ret;
    }

    /**
     * @param  Iterator      $iter
     * @return ArrayIterator
     */
    protected function exportFiles(Iterator $iter)
    {
        $ret = new ArrayIterator(array());
        foreach ($iter as $file) {

            $resources = new ArrayIterator(array($file->getResource()));

            $file = File::create(
                array(
                    'id' => $file->getId(),
                    'folder_id' => $file->getFolder() ? $file->getFolder()->getId() : null,
                    'profile' => $file->getProfile(),
                    'name' => $file->getName(),
                    'date_created' => $file->getDateCreated(),
                    'status' => $file->getStatus(),
                    'uuid' => $file->getUuid(),
                    'resource' => $this->exportResources($resources)->current(),
                    'data' => $file->getData(),
                )
            );

            $this->setVersions($file);

            $ret->append(
                $file
            );
        }

        return $ret;
    }

    /**
     * @param  Iterator      $iter
     * @return ArrayIterator
     */
    protected function exportResources(Iterator $iter)
    {
        $ret = new ArrayIterator(array());
        foreach ($iter as $resource) {
            $ret->append(
                ConcreteResource::create(
                    array(
                        'id' => $resource->getId(),
                        'uuid' => $resource->getUuid(),
                        'hash' => $resource->getHash(),
                        'date_created' => $resource->getDateCreated(),
                        'data' => $resource->getData(),
                        'mimetype' => $resource->getMimetype(),
                        'size' => $resource->getSize(),
                    )
                )
            );
        }

        return $ret;
    }

    /**
     * @return Connection
     */
    protected function getConnection()
    {
        return $this->em->getConnection();
    }

    protected function setVersions(Versionable $versionable)
    {
        $versions = $this->em->getRepository(Versioned::class)->findBy([
            'uuid' => $versionable->getUuid()
        ]);

        foreach ($versions as $v) {
            /** @var Versioned $v */
            $versionable->addVersion(
                new Versioned(
                    $v->getUuid(),
                    $v->getVersion()
                ),
                $this->exportResources(new ArrayIterator([$v->getResource()]))->current()
            );
        }

    }
}
