<?php

namespace Xi\Filelib\Backend;

use Exception,
    Xi\Filelib\FileLibrary,
    Xi\Filelib\File\File,
    Xi\Filelib\Folder\Folder,
    Xi\Filelib\FilelibException,
    Doctrine\ORM\EntityManager,
    Doctrine\ORM\NoResultException,
    Doctrine\ORM\EntityNotFoundException;

/**
 * Doctrine 2 backend for filelib
 *
 * @category Xi
 * @package  Xi_Filelib
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
     * Finds a file
     *
     * @param  integer          $id
     * @return array|false
     * @throws FilelibException
     */
    public function findFile($id)
    {
        $this->assertValidIdentifier($id);

        try {
            $file = $this->em->find($this->fileEntityName, $id);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }

        if (!$file) {
            return false;
        }

        return $this->_fileToArray($file);
    }

    /**
     * @param  Folder           $folder
     * @param  string           $filename
     * @return array
     * @throws FilelibException
     */
    public function findFileByFilename(Folder $folder, $filename)
    {
        $this->assertValidIdentifier($folder->getId());

        try {
            $qb = $this->em->createQueryBuilder();

            $qb->select('f')
               ->from($this->fileEntityName, 'f')
               ->where('f.folder = :folder')
               ->andWhere('f.name = :filename')
               ->setParameter('folder', $folder->getId())
               ->setParameter('filename', $filename);

            $file = $qb->getQuery()->getResult();

            if (!$file) {
                return false;
            }

            return $this->_fileToArray($file[0]);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Finds all files
     *
     * @return array
     */
    public function findAllFiles()
    {
        $qb = $this->em->createQueryBuilder();

        $qb->select('f')
           ->from($this->fileEntityName, 'f')
           ->orderBy('f.id', 'ASC');

        return array_map(
            array($this, '_fileToArray'),
            $qb->getQuery()->getResult()
        );
    }

    /**
     * Finds files in folder
     *
     * @param  Folder $folder
     * @return array
     */
    public function findFilesIn(Folder $folder)
    {
        $this->assertValidIdentifier($folder->getId());

        $qb = $this->em->createQueryBuilder();

        $qb->select('f')
           ->from($this->fileEntityName, 'f')
           ->where('f.folder = :folder')
           ->setParameter('folder', $folder->getId());

        return array_map(
            array($this, '_fileToArray'),
            $qb->getQuery()->getResult()
        );
    }

    /**
     * Updates a file
     *
     * @param  File             $file
     * @return boolean
     * @throws FilelibException When fails
     */
    public function updateFile(File $file)
    {
        try {
            $fileRow = $this->getFileReference($file);
            $fileRow->setFolder($this->getFolderReference($file->getFolderId()));
            $fileRow->setMimetype($file->getMimetype());
            $fileRow->setProfile($file->getProfile());
            $fileRow->setSize($file->getSize());
            $fileRow->setName($file->getName());
            $fileRow->setLink($file->getLink());
            $fileRow->setDateUploaded($file->getDateUploaded());

            $this->em->flush();

            return true;
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Deletes a file
     *
     * @param  File             $file
     * @return boolean
     * @throws FilelibException When fails
     */
    public function deleteFile(File $file)
    {
        $this->assertValidIdentifier($file->getId());

        try {
            $this->em->remove($this->getFileReference($file));
            $this->em->flush();

            return true;
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Finds folder
     *
     * @param  integer     $id
     * @return array|false
     */
    public function findFolder($id)
    {
        $this->assertValidIdentifier($id);

        try {
            $folder = $this->em->find($this->folderEntityName, $id);
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }

        if (!$folder) {
            return false;
        }

        return $this->_folderToArray($folder);
    }

    /**
     * Finds folder by url
     *
     * @param  string      $url
     * @return array|false
     */
    public function findFolderByUrl($url)
    {
        $folder = $this->em->getRepository($this->folderEntityName)->findOneBy(array(
            'url' => $url,
        ));

        if (!$folder) {
            return false;
        }

        return $this->_folderToArray($folder);
    }

    /**
     * Finds the root folder
     *
     * @return array
     */
    public function findRootFolder()
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

        return $this->_folderToArray($folder);
    }

    /**
     * Finds subfolders of a folder
     *
     * @param  Folder $folder
     * @return array
     */
    public function findSubFolders(Folder $folder)
    {
        $this->assertValidIdentifier($folder->getId());

        try {
            $qb = $this->em->createQueryBuilder();

            $qb->select('f')
               ->from($this->folderEntityName, 'f')
               ->where('f.parent = :folder')
               ->setParameter('folder', $folder->getId());

            return array_map(
                array($this, '_folderToArray'),
                $qb->getQuery()->getResult()
            );
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Creates a folder
     *
     * @param  Folder           $folder
     * @return Folder           Created folder
     * @throws FilelibException When fails
     */
    public function createFolder(Folder $folder)
    {
        try {
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
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Updates a folder
     *
     * @param  Folder           $folder
     * @return boolean
     * @throws FilelibException When fails
     */
    public function updateFolder(Folder $folder)
    {
        $this->assertValidIdentifier($folder->getId());

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
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * Deletes a folder
     *
     * @param  Folder           $folder
     * @return boolean
     * @throws FilelibException When fails
     */
    public function deleteFolder(Folder $folder)
    {
        try {
            $folderEntity = $this->em->find($this->folderEntityName, $folder->getId());

            if (!$folderEntity) {
                return false;
            }

            $this->em->remove($folderEntity);
            $this->em->flush();

            return true;
        } catch (EntityNotFoundException $e) {
            return false;
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * @param  File   $upload
     * @param  Folder $folder
     * @return File
     */
    public function upload(File $upload, Folder $folder)
    {
        try {
            $self = $this;

            return $this->em->transactional(function(EntityManager $em) use ($self, $upload, $folder) {
                $fileEntityName = $self->getFileEntityName();

                $file = new $fileEntityName;
                $file->setFolder($self->getFolderReference($folder->getId()));
                $file->setMimetype($upload->getMimeType());
                $file->setSize($upload->getSize());
                $file->setName($upload->getName());
                $file->setProfile($upload->getProfile());
                $file->setDateUploaded($upload->getDateUploaded());

                $em->persist($file);
                $em->flush();

                $upload->setId($file->getId());
                $upload->setFolderId($file->getFolder()->getId());

                return $upload;
            });
        } catch (Exception $e) {
            throw new FilelibException($e->getMessage());
        }
    }

    /**
     * File to array
     *
     * @param  object $file
     * @return array
     */
    private function _fileToArray($file)
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
        );
    }

    /**
     * Folder to array
     *
     * @param  object $folder
     * @return array
     */
    private function _folderToArray($folder)
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
     * @param  mixed            $id
     * @throws FilelibException
     */
    private function assertValidIdentifier($id)
    {
        if (!is_numeric($id)) {
            throw new FilelibException(sprintf(
                'Id must be numeric; %s given.',
                $id
            ));
        }
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
