<?php

namespace Xi\Filelib\Backend;

/**
 * Doctrine 2 backend for filelib
 *
 * @category Xi
 * @package  Xi_Filelib
 * @author   Mikko Hirvonen
 * @author   pekkis
 */
class Doctrine2Backend extends AbstractBackend
{
    /**
     * File entity name
     *
     * @var string
     */
    private $_fileEntityName = 'Xi\Filelib\Integration\Symfony\FilelibBundle\Entity\File';

    /**
     * Folder entity name
     *
     * @var string
     */
    private $_folderEntityName = 'Xi\Filelib\Integration\Symfony\FilelibBundle\Entity\Folder';

    /**
     * Entity manager
     *
     * @var \Doctrine\ORM\EntityManager
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
     * @param \Doctrine\Orm\EntityManager $em
     */
    public function setEntityManager(\Doctrine\Orm\EntityManager $em)
    {
        $this->_em = $em;
    }
    
    
    /**
     * Returns the entity manager
     * 
     * @return null
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
     * @param  \Xi_Filelib                   $filelib
     * @return \Xi\Filelib\Backend\Doctrine2Backend
     */
    public function setFilelib(\Xi\Filelib\FileLibrary $filelib)
    {
        $this->_filelib = $filelib;

        return $this;
    }

    /**
     * Returns filelib
     *
     * @return \Xi\Filelib\FileLibrary Filelib
     */
    public function getFilelib()
    {
        return $this->_filelib;
    }

    /**
     * Finds a file
     *
     * @param  integer                        $id
     * @return array|false
     */
    public function findFile($id)
    {
        $file = $this->_em->find($this->_fileEntityName, $id);

        if (!$file) {
            return false;
        }

        return $this->_fileToArray($file);
    }


    public function findFileByFilename(\Xi\Filelib\Folder\Folder $folder, $filename)
    {
        $folderEntity = $this->_em->find($this->_folderEntityName, $folder->getId());
                
        $query = $this->_em->createQuery(
            'SELECT f FROM ' . $this->getFileEntityName() . ' f WHERE f.folder = :folder
             AND f.name = :filename'
        );
        
        $query->setParameter('folder', $folderEntity);
                        
        $query->setParameter('filename', $filename);

        $file = $query->getResult();

        
        if (!$file) {
            return false;
        }

        return $this->_fileToArray($file[0]);
    }
    
    
    
    /**
     * Finds all files
     *
     * @return \Xi\Filelib\File\FileIterator
     */
    public function findAllFiles()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('f')
        ->from($this->_fileEntityName, 'f')
        ->orderBy('f.id', 'ASC');

        $files = array();

        foreach ($qb->getQuery()->getResult() as $file) {
            $files[] = $this->_fileToArray($file);
        }

        return $files;
    }

    /**
     * Finds a file
     *
     * @param  \Xi\Filelib\Folder\Folder       $folder
     * @return \Xi\Filelib\File\FileIterator
     */
    public function findFilesIn(\Xi\Filelib\Folder\Folder $folder)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('f')
        ->from($this->_fileEntityName, 'f')
        ->where('f.folder = :folder');

        $qb->setParameter('folder', $folder->getId());

        $files = array();
        
        foreach ($qb->getQuery()->getResult() as $file) {
            $files[] = $this->_fileToArray($file);
        }

        return $files;
    }

    /**
     * Updates a file
     *
     * @param  \Xi\Filelib\File\File  $file
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function updateFile(\Xi\Filelib\File\File $file)
    {
        try {
            $file->setLink($file->getProfileObject()->getLinker()->getLink($file, true));
                        
            $fileRow = $this->_em->getReference($this->_fileEntityName,
            $file->getId());
                        
            $fileRow->setFolder($this->_em->getReference($this->_folderEntityName,
            $file->getFolderId()));

            $fileRow->setMimetype($file->getMimetype());
            $fileRow->setProfile($file->getProfile());
            $fileRow->setSize($file->getSize());
            $fileRow->setName($file->getName());
            $fileRow->setLink($file->getLink());
            $fileRow->setDateUploaded($file->getDateUploaded());
            
            $this->_em->flush();
            
        } catch (Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }
        
    }

    /**
     * Deletes a file
     *
     * @param  \Xi\Filelib\File\File  $file
     * @throws \Xi\Filelib\FilelibException When fails
     */
    public function deleteFile(\Xi\Filelib\File\File $file)
    {
        try {
            $fileRow = $this->_em->getReference($this->_fileEntityName, $file->getId());
            $this->_em->remove($fileRow);
            $this->_em->flush();
        } catch (Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }
    }

    /**
     * Finds folder
     *
     * @param  integer                          $id
     * @return array|false
     */
    public function findFolder($id)
    {
        $folder = $this->_em->find($this->_folderEntityName, $id);

        if(!$folder) {
            return false;
        }
                
        return $this->_folderToArray($folder);
    }

    /**
     * Finds folder by url
     *
     * @param  integer                          $id
     * @return \Xi\Filelib\Folder\Folder|false
     */
    public function findFolderByUrl($url)
    {
        $folder = $this->_em->getRepository($this->_folderEntityName)->findOneBy(array(
            'url' => $url,
        ));
                
        if(!$folder) {
            return false;
        }
                
        return $this->_folderToArray($folder);
    }
    
    
    
    /**
     * Finds the root folder
     *
     * @return \Xi\Filelib\Folder\Folder
     */
    public function findRootFolder()
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('f')
        ->from($this->_folderEntityName, 'f')
        ->where('f.parent IS NULL');

        try {
            $folder = $qb->getQuery()->getSingleResult();    
        } catch(\Doctrine\ORM\NoResultException $e) {
            
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
     * @param  \Xi\Filelib\Folder\Folder         $id
     * @return \Xi\Filelib\Folder\FolderIterator
     */
    public function findSubFolders(\Xi\Filelib\Folder\Folder $folder)
    {
        $qb = $this->_em->createQueryBuilder();

        $qb->select('f')
        ->from($this->_folderEntityName, 'f')
        ->where('f.parent = :folder');

        $qb->setParameter('folder', $folder->getId());

        $folders = array();

        foreach ($qb->getQuery()->getResult() as $folderRow) {
            $folders[] = $this->_folderToArray($folderRow);
        }

        return $folders;
    }

    /**
     * Creates a folder
     *
     * @param  \Xi\Filelib\Folder\Folder $folder
     * @return \Xi\Filelib\Folder\Folder Created folder
     * @throws \Xi\Filelib\FilelibException  When fails
     */
    public function createFolder(\Xi\Filelib\Folder\Folder $folder)
    {
        try {
            $folderRow = new $this->_folderEntityName();

            if ($folder->getParentId()) {
                $folderRow->setParent($this->_em->getReference($this->_folderEntityName,
                $folder->getParentId()));
            }

            $folderRow->setName($folder->getName());
            $folderRow->setUrl($folder->getUrl());
                        
            $this->_em->persist($folderRow);
            $this->_em->flush();

            $folder->setId($folderRow->getId());

            return $folder;
        } catch (Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }
    }

    /**
     * Updates a folder
     *
     * @param  \Xi\Filelib\Folder\Folder $folder
     * @throws \Xi\Filelib\FilelibException  When fails
     */
    public function updateFolder(\Xi\Filelib\Folder\Folder $folder)
    {
        try {
            $folderRow = $this->_em->getReference($this->_folderEntityName,
            $folder->getId());

            if ($folder->getParentId()) {
                $folderRow->setParent($this->_em->getReference($this->_folderEntityName,
                $folder->getParentId()));
            } else {
                $folderRow->removeParent();
            }

            $folderRow->setName($folder->getName());
            $folderRow->setUrl($folder->getUrl());
            

            $this->_em->flush();
            
        } catch (Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }
    }

    /**
     * Deletes a folder
     *
     * @param  \Xi\Filelib\Folder\Folder $folder
     * @throws \Xi\Filelib\FilelibException  When fails
     */
    public function deleteFolder(\Xi\Filelib\Folder\Folder $folder)
    {
        try {
            $folder = $this->_em->getReference($this->_folderEntityName, $folder->getId());

            $this->_em->remove($folder);
            $this->_em->flush();
        } catch (Exception $e) {
            throw new \Xi\Filelib\FilelibException($e->getMessage());
        }
    }

    /**
     * Uploads a file
     *
     * @param  \Xi\Filelib\File\Upload\FileUpload $upload Fileobject to upload
     * @param  \Xi\Filelib\Folder\Folder $folder Folder
     * @return \Xi\Filelib\File\File   File item
     * @throws \Xi\Filelib\FilelibException  When fails
     */
    public function upload(\Xi\Filelib\File\Upload\FileUpload $upload,
    \Xi\Filelib\Folder\Folder $folder,
    \Xi\Filelib\File\FileProfile $profile
    ){
        try {
            
            $conn = $this->_em->getConnection();
            $conn->beginTransaction();

            $file = new $this->_fileEntityName();

            $file->setFolder($this->_em->getReference($this->_folderEntityName,
            $folder->getId()));
            $file->setMimetype($upload->getMimeType());
            $file->setSize($upload->getSize());
            $file->setName($upload->getOverrideFilename());
            $file->setProfile($profile->getIdentifier());
            $file->setDateUploaded($upload->getDateUploaded());
            
            $this->_em->persist($file);

            $this->_em->flush();

            $conn->commit();
            
            return $this->_fileToArray($file);
            
        } catch (Exception $e) {
            $conn->rollback();

            throw new \Xi\Filelib\FilelibException($e->getMessage());
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
            'id'        => $file->getId(),
            'folder_id' => $file->getFolder() ? $file->getFolder()->getId() : null,
            'mimetype'  => $file->getMimetype(),
            'profile'   => $file->getProfile(),
            'size'      => $file->getSize(),
            'name'      => $file->getName(),
            'link'      => $file->getLink(),
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
            'parent_id' => $folder->getParent() ? $folder->getParent()->getId() : null,
            'name'      => $folder->getName(),
            'url' => $folder->getUrl(),
        );
    }
}
