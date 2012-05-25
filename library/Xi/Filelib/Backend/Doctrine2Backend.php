<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Exception\NonUniqueFileException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\EntityNotFoundException;
use PDOException;

/**
 * Doctrine 2 backend for filelib
 *
 * @category Xi
 * @package  Filelib
 * @author   Mikko Hirvonen <mikko.petteri.hirvonen@gmail.com>
 * @author   pekkis
 */
class Doctrine2Backend extends AbstractBackend
{
    /**
     * File entity name
     *
     * @var string
     */
    private $fileEntityName = 'Xi\Filelib\Backend\Doctrine2\Entity\File';

    /**
     * Folder entity name
     *
     * @var string
     */
    private $folderEntityName = 'Xi\Filelib\Backend\Doctrine2\Entity\Folder';

    /**
     * Resource entity name
     *
     * @var string
     */
    private $resourceEntityName = 'Xi\Filelib\Backend\Doctrine2\Entity\Resource';


    /**
     * Entity manager
     *
     * @var EntityManager
     */
    private $em;

    /**
     * @param  EntityManager    $em
     * @return Doctrine2Backend
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
     * @param  integer    $id
     * @return array|null
     */
    protected function doFindFile($id)
    {
        return $this->em->find($this->fileEntityName, $id);
    }

    /**
     * @param  Folder     $folder
     * @param  string     $filename
     * @return array|null
     */
    public function doFindFileByFilename(Folder $folder, $filename)
    {
        return $this->em->getRepository($this->fileEntityName)->findOneBy(array(
            'folder' => $folder->getId(),
            'name'   => $filename,
        ));
    }

    /**
     * @return array
     */
    protected function doFindAllFiles()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('f')
           ->from($this->fileEntityName, 'f')
           ->orderBy('f.id', 'ASC');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param  integer $id
     * @return array
     */
    protected function doFindFilesIn($id)
    {
        return $this->em->getRepository($this->fileEntityName)->findBy(array(
            'folder' => $id,
        ));
    }

    /**
     * @param  File    $file
     * @return boolean
     */
    protected function doUpdateFile(File $file)
    {
        $entity = $this->getFileReference($file);
        $entity->setFolder($this->getFolderReference($file->getFolderId()));
        $entity->setMimetype($file->getMimetype());
        $entity->setProfile($file->getProfile());
        $entity->setSize($file->getSize());
        $entity->setName($file->getName());
        $entity->setLink($file->getLink());
        $entity->setDateUploaded($file->getDateUploaded());
        $entity->setStatus($file->getStatus());
        $entity->setUuid($file->getUuid());
        $entity->setResource($this->em->getReference($this->getResourceEntityName(), $file->getResource()->getId()));

        $this->em->flush();

        return true;
    }

    /**
     * @param  File    $file
     * @return boolean
     */
    protected function doDeleteFile(File $file)
    {
        if (!$entity = $this->em->find($this->fileEntityName, $file->getId())) {
            return false;
        }

        $this->em->remove($entity);
        $this->em->flush();

        return true;
    }

    /**
     * @param  integer     $id
     * @return Folder|null
     */
    protected function dofindFolder($id)
    {
        return $this->em->find($this->folderEntityName, $id);
    }

    /**
     * @param  string     $url
     * @return array|null
     */
    protected function doFindFolderByUrl($url)
    {
        return $this->em->getRepository($this->folderEntityName)->findOneBy(array(
            'url' => $url,
        ));
    }

    /**
     * @return object Folder entity
     */
    protected function doFindRootFolder()
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('f')
           ->from($this->folderEntityName, 'f')
           ->where('f.parent IS NULL');

        try {
            $folder = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            $className = $this->getFolderEntityName();

            $folder = new $className();
            $folder->setName('root');
            $folder->setUrl('');
            $folder->removeParent();
            $folder->setUuid($this->generateUuid());

            $this->em->persist($folder);
            $this->em->flush();
        }

        return $folder;
    }

    /**
     * @param  integer $id
     * @return array
     */
    protected function doFindSubFolders($id)
    {
        return $this->em->getRepository($this->folderEntityName)->findBy(array(
            'parent' => $id,
        ));
    }

    /**
     * @param  Folder $folder
     * @return Folder
     */
    protected function doCreateFolder(Folder $folder)
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
     * @param  Folder  $folder
     * @return boolean
     */
    protected function doUpdateFolder(Folder $folder)
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
     * @param Resource $resource
     * @return boolean
     */
    protected function doUpdateResource(Resource $resource)
    {
        try {
            $resourceRow = $this->em->getReference($this->getResourceEntityName(), $resource->getId());
            $resourceRow->setVersions($resource->getVersions());
            $this->em->flush();
            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        }
    }



    /**
     * @param  Folder  $folder
     * @return boolean
     */
    protected function doDeleteFolder(Folder $folder)
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
     * @param  Resource  $resource
     * @return boolean
     */
    protected function doDeleteResource(Resource $resource)
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
     * @param  Resource           $resource
     * @return Resource
     * @throws FilelibException
     */
    protected function doCreateResource(Resource $resource)
    {
        $resourceRow = new $this->resourceEntityName();
        $resourceRow->setHash($resource->getHash());
        $resourceRow->setDateCreated($resource->getDateCreated());
        $this->em->persist($resourceRow);
        $this->em->flush();
        $resource->setId($resourceRow->getId());
        return $resource;
    }


    /**
     * @param  string     $hash
     * @return array|null
     */
    public function doFindResourcesByHash($hash)
    {
        return $this->em->getRepository($this->resourceEntityName)->findBy(array(
            'hash'   => $hash,
        ));
    }


    /**
     * @param  integer     $id
     * @return Resource|null
     */
    protected function dofindResource($id)
    {
        return $this->em->find($this->resourceEntityName, $id);
    }




    /**
     * @param  File                   $file
     * @param  Folder                 $folder
     * @return File
     * @throws NonUniqueFileException If file already exists folder
     */
    protected function doUpload(File $file, Folder $folder)
    {
        $self = $this;

        return $this->em->transactional(function(EntityManager $em) use ($self, $file, $folder) {
            $fileEntityName = $self->getFileEntityName();

            $entity = new $fileEntityName;
            $entity->setFolder($self->getFolderReference($folder->getId()));
            $entity->setMimetype($file->getMimeType());
            $entity->setSize($file->getSize());
            $entity->setName($file->getName());
            $entity->setProfile($file->getProfile());
            $entity->setDateUploaded($file->getDateUploaded());
            $entity->setStatus($file->getStatus());
            $entity->setUuid($file->getUuid());

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
     * File to array
     *
     * @param  File  $file
     * @return array
     */
    protected function fileToArray($file)
    {
        return array(
            'id'            => $file->getId(),
            'folder_id'     => $file->getFolder()
                                   ? $file->getFolder()->getId()
                                   : null,
            'mimetype'      => $file->getMimetype(),
            'profile'       => $file->getProfile(),
            'size'          => $file->getSize(),
            'name'          => $file->getName(),
            'link'          => $file->getLink(),
            'date_uploaded' => $file->getDateUploaded(),
            'status'        => $file->getStatus(),
            'uuid'          => $file->getUuid(),
            'resource' => ($file->getResource()) ? $this->resourceToArray($file->getResource()) : null
        );
    }

    /**
     * Folder to array
     *
     * @param  Folder $folder
     * @return array
     */
    protected function folderToArray($folder)
    {
        return array(
            'id'        => $folder->getId(),
            'parent_id' => $folder->getParent()
                               ? $folder->getParent()->getId()
                               : null,
            'name'      => $folder->getName(),
            'url'       => $folder->getUrl(),
            'uuid'      => $folder->getUuid(),
        );
    }


    protected function resourceToArray($resource)
    {
        return Resource::create(array(
            'id' => $resource->getId(),
            'hash' => $resource->getHash(),
            'date_created' => $resource->getDateCreated(),
            'versions' => $resource->getVersions(),
        ));
    }


    public function doGetNumberOfReferences(Resource $resource)
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


    public function isValidIdentifier($id)
    {
        return (is_numeric($id));
    }

}
