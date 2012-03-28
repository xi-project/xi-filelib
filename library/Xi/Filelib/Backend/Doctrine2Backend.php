<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\File\File,
    Xi\Filelib\Folder\Folder,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\NoResultException,
    Doctrine\ORM\EntityNotFoundException;

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
        $folderRow = new $this->folderEntityName();

        if ($folder->getParentId()) {
            $folderRow->setParent($this->getFolderReference(
                $folder->getParentId()
            ));
        }

        $folderRow->setName($folder->getName());
        $folderRow->setUrl($folder->getUrl());

        $this->em->persist($folderRow);
        $this->em->flush();

        $folder->setId($folderRow->getId());

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
     * @param  File   $file
     * @param  Folder $folder
     * @return File
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

            $em->persist($entity);
            $em->flush();

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
        );
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
}
