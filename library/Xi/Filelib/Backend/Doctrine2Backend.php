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
    private $_fileEntityName = 'Xi\Filelib\Backend\Doctrine2\Entity\File';

    /**
     * Folder entity name
     *
     * @var string
     */
    private $_folderEntityName = 'Xi\Filelib\Backend\Doctrine2\Entity\Folder';

    /**
     * Entity manager
     *
     * @var EntityManager
     */
    private $_em;

    /**
     * Sets the fully qualified file entity classname
     *
     * @param string $fileEntityName
     */
    public function setFileEntityName($fileEntityName)
    {
        $this->_fileEntityName = $fileEntityName;
    }

    /**
     * Returns the fully qualified file entity classname
     *
     * @return string
     */
    public function getFileEntityName()
    {
        return $this->_fileEntityName;
    }

    /**
     * Sets the entity manager
     *
     * @param EntityManager $em
     */
    public function setEntityManager(EntityManager $em)
    {
        $this->_em = $em;
    }

    /**
     * Returns the entity manager
     *
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->_em;
    }

    /**
     * Sets the fully qualified folder entity classname
     *
     * @param string $folderEntityName
     */
    public function setFolderEntityName($folderEntityName)
    {
        $this->_folderEntityName = $folderEntityName;
    }

    /**
     * Returns the fully qualified folder entity classname
     *
     * @return string
     */
    public function getFolderEntityName()
    {
        return $this->_folderEntityName;
    }

    /**
     * Sets filelib
     *
     * @param  FileLibrary      $filelib
     * @return Doctrine2Backend
     */
    public function setFilelib(FileLibrary $filelib)
    {
        $this->_filelib = $filelib;

        return $this;
    }

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib()
    {
        return $this->_filelib;
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
            $file = $this->_em->find($this->_fileEntityName, $id);
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
            $folderEntity = $this->_em->find($this->_folderEntityName, $folder->getId());

            $qb = $this->_em->createQueryBuilder();

            $qb->select('f')
               ->from($this->_fileEntityName, 'f')
               ->where('f.folder = :folder')
               ->andWhere('f.name = :filename')
               ->setParameter('folder', $folderEntity)
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
        $qb = $this->_em->createQueryBuilder();

        $qb->select('f')
           ->from($this->_fileEntityName, 'f')
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

        $qb = $this->_em->createQueryBuilder();

        $qb->select('f')
           ->from($this->_fileEntityName, 'f')
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

            $this->_em->flush();

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
            $this->_em->remove($this->getFileReference($file));
            $this->_em->flush();

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
            $folder = $this->_em->find($this->_folderEntityName, $id);
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
        $folder = $this->_em->getRepository($this->_folderEntityName)->findOneBy(array(
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
        $qb = $this->_em->createQueryBuilder();

        $qb->select('f')
           ->from($this->_folderEntityName, 'f')
           ->where('f.parent IS NULL');

        try {
            $folder = $qb->getQuery()->getSingleResult();
        } catch (NoResultException $e) {
            $className = $this->getFolderEntityName();

            $folder = new $className();
            $folder->setName('root');
            $folder->setUrl('');
            $folder->removeParent();

            $this->_em->persist($folder);
            $this->_em->flush();
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
            $qb = $this->_em->createQueryBuilder();

            $qb->select('f')
               ->from($this->_folderEntityName, 'f')
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
            $folderRow = new $this->_folderEntityName();

            if ($folder->getParentId()) {
                $folderRow->setParent($this->getFolderReference(
                    $folder->getParentId()
                ));
            }

            $folderRow->setName($folder->getName());
            $folderRow->setUrl($folder->getUrl());

            $this->_em->persist($folderRow);
            $this->_em->flush();

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

            $this->_em->flush();

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
            $folderEntity = $this->_em->find($this->_folderEntityName, $folder->getId());

            if (!$folderEntity) {
                return false;
            }

            $this->_em->remove($folderEntity);
            $this->_em->flush();

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

            return $this->_em->transactional(function(EntityManager $em) use ($self, $upload, $folder) {
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
        return $this->_em->getReference($this->_fileEntityName, $file->getId());
    }

    /**
     * NOTE: Should be private!
     *
     * @param  integer     $id
     * @return object|null
     */
    public function getFolderReference($id)
    {
        return $this->_em->getReference($this->_folderEntityName, $id);
    }
}
