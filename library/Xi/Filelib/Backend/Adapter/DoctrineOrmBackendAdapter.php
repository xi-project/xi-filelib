<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter;

use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Folder\Folder;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Xi\Filelib\Backend\Finder\Finder;
use PDO;
use Iterator;
use ArrayIterator;

/**
 * Doctrine 2 backend for filelib
 *
 * @category Xi
 * @package  Filelib
 * @author   Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 * @author   pekkis
 */
class DoctrineOrmBackendAdapter implements BackendAdapter
{
    /**
     * @var string
     */
    private $fileEntityName = 'Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity\File';

    /**
     * @var string
     */
    private $folderEntityName = 'Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity\Folder';

    /**
     * @var string
     */
    private $resourceEntityName = 'Xi\Filelib\Backend\Adapter\DoctrineOrm\Entity\Resource';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var array
     */
    private $finderMap = array(
        'Xi\Filelib\Resource\Resource' => array(
            'id' => 'id',
            'hash' => 'hash',
        ),
        'Xi\Filelib\File\File' => array(
            'id' => 'id',
            'folder_id' => 'folder_id',
            'name' => 'filename',
        ),
        'Xi\Filelib\Folder\Folder' => array(
            'id' => 'id',
            'parent_id' => 'parent_id',
            'url' => 'folderurl',
        ),
    );

    private $classNameToResources = array(
        'Xi\Filelib\Resource\Resource' => array(
            'table' => 'xi_filelib_resource',
            'exporter' => 'exportResources',
            'getEntityName' => 'getResourceEntityName',
        ),
        'Xi\Filelib\File\File' => array(
            'table' => 'xi_filelib_file',
            'exporter' => 'exportFiles',
            'getEntityName' => 'getFileEntityName',
        ),
        'Xi\Filelib\Folder\Folder' => array(
            'table' => 'xi_filelib_folder',
            'exporter' => 'exportFolders',
            'getEntityName' => 'getFolderEntityName',
        ),
    );

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function isOrigin()
    {
        return true;
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
     * Returns the entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->em;
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
     * Returns the fully qualified resource entity classname
     *
     * @return string
     */
    public function getResourceEntityName()
    {
        return $this->resourceEntityName;
    }

    /**
     * @see BackendAdapter::updateFile
     */
    public function updateFile(File $file)
    {
        $entity = $this->getFileReference($file);
        $entity->setFolder($this->getFolderReference($file->getFolderId()));
        $entity->setProfile($file->getProfile());
        $entity->setName($file->getName());
        $entity->setDateCreated($file->getDateCreated());
        $entity->setStatus($file->getStatus());
        $entity->setUuid($file->getUuid());
        $entity->setResource($this->em->getReference($this->getResourceEntityName(), $file->getResource()->getId()));
        $entity->setData($file->getData()->getArrayCopy());

        $this->em->flush($entity);
        return true;
    }

    /**
     * @see BackendAdapter::deleteFile
     */
    public function deleteFile(File $file)
    {
        if (!$entity = $this->em->find($this->fileEntityName, $file->getId())) {
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
        $folderEntity = new $this->folderEntityName();

        if ($folder->getParentId()) {
            $folderEntity->setParent($this->getFolderReference($folder->getParentId()));
        }

        $folderEntity->setName($folder->getName());
        $folderEntity->setUrl($folder->getUrl());
        $folderEntity->setUuid($folder->getUuid());

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
            $folderRow = $this->getFolderReference($folder->getId());

            if ($folder->getParentId()) {
                $folderRow->setParent(
                    $this->getFolderReference($folder->getParentId())
                );
            } else {
                $folderRow->removeParent();
            }

            $folderRow->setName($folder->getName());
            $folderRow->setUrl($folder->getUrl());
            $folderRow->setUuid($folder->getUuid());

            $this->em->flush($folderRow);

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }

    /**
     * @see BackendAdapter::updateResource
     */
    public function updateResource(Resource $resource)
    {
        try {
            $resourceRow = $this->em->getReference($this->getResourceEntityName(), $resource->getId());
            $resourceRow->setData($resource->getData()->getArrayCopy());
            $resourceRow->setExclusive($resource->isExclusive());
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
            $folderEntity = $this->em->find($this->folderEntityName, $folder->getId());

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
    public function deleteResource(Resource $resource)
    {
        try {
            $entity = $this->em->find($this->resourceEntityName, $resource->getId());

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
    public function createResource(Resource $resource)
    {
        $resourceRow = new $this->resourceEntityName();
        $resourceRow->setHash($resource->getHash());
        $resourceRow->setDateCreated($resource->getDateCreated());
        $resourceRow->setMimetype($resource->getMimetype());
        $resourceRow->setSize($resource->getSize());
        $resourceRow->setExclusive($resource->isExclusive());
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
        $self = $this;

        return $this->em->transactional(
            function (EntityManager $em) use ($self, $file, $folder) {
                $fileEntityName = $self->getFileEntityName();

                $entity = new $fileEntityName;
                $entity->setFolder($self->getFolderReference($folder->getId()));
                $entity->setName($file->getName());
                $entity->setProfile($file->getProfile());
                $entity->setDateCreated($file->getDateCreated());
                $entity->setStatus($file->getStatus());
                $entity->setUuid($file->getUuid());
                $entity->setData($file->getData()->getArrayCopy());

                $resource = $file->getResource();
                if ($resource) {
                    $entity->setResource($em->getReference($self->getResourceEntityName(), $resource->getId()));
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
    public function getNumberOfReferences(Resource $resource)
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
     * @see BackendAdapter::findByFinder
     */
    public function findByFinder(Finder $finder)
    {
        $resources = $this->classNameToResources[$finder->getResultClass()];
        $params = $this->finderParametersToInternalParameters($finder);

        $tableName = $resources['table'];
        $conn = $this->em->getConnection();

        $qb = $conn->createQueryBuilder();
        $qb->select("id")->from($tableName, 't');

        $bindParams = array();
        foreach ($params as $param => $value) {

            if ($value === null) {
                $qb->andWhere("t.{$param} IS NULL");
            } else {
                $qb->andWhere("t.{$param} = :{$param}");
                $bindParams[$param] = $value;
            }

        }

        $sql = $qb->getSQL();
        $stmt = $conn->prepare($sql);
        foreach ($bindParams as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();

        $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            function ($ret) {
                return $ret['id'];
            },
            $ret
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
        $repo = $this->em->getRepository($this->$resources['getEntityName']());
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

            $ret->append(
                File::create(
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
                )
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
                Resource::create(
                    array(
                        'id' => $resource->getId(),
                        'hash' => $resource->getHash(),
                        'date_created' => $resource->getDateCreated(),
                        'data' => $resource->getData(),
                        'mimetype' => $resource->getMimetype(),
                        'size' => $resource->getSize(),
                        'exclusive' => $resource->getExclusive(),
                    )
                )
            );
        }

        return $ret;
    }

    /**
     * @param  File        $file
     * @return object|null
     */
    public function getFileReference(File $file)
    {
        return $this->em->getReference($this->fileEntityName, $file->getId());
    }

    /**
     * @param  integer     $id
     * @return object|null
     */
    public function getFolderReference($id)
    {
        return $this->em->getReference($this->folderEntityName, $id);
    }

    /**
     * @param  Finder $finder
     * @return array
     */
    protected function finderParametersToInternalParameters(Finder $finder)
    {
        $ret = array();
        foreach ($finder->getParameters() as $key => $value) {
            $ret[$this->finderMap[$finder->getResultClass()][$key]] = $value;
        }

        return $ret;
    }
}
