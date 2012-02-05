<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\File\File,
    Xi\Filelib\Folder\Folder,
    Xi\Filelib\FilelibException,
    MongoDb,
    MongoId,
    MongoDate,
    DateTime,
    MongoCursorException;

/**
 * MongoDB backend for Filelib
 * 
 * @author pekkis
 * @package Xi_Filelib
 * @todo Prototype, to be error-proofed
 *
 */
class MongoBackend extends AbstractBackend implements Backend
{
    
    /**
     * MongoDB reference
     * 
     * @var \MongoDB
     */
    private $_mongo;
    
    /**
     * Sets MongoDB
     * 
     * @param \MongoDB $mongo
     */
    public function setMongo(\MongoDB $mongo)
    {
        $this->_mongo = $mongo;
    }
    
    
    /**
     * Returns MongoDB
     * 
     * @return \MongoDB
     */
    public function getMongo()
    {
        return $this->_mongo;
    }
    
    
    /**
     * Finds folder
     *
     * @param  string     $id
     * @return array|null
     */
    protected function doFindFolder($id)
    {
        return $this->getMongo()->folders->findOne(array(
            '_id' => new MongoId($id),
        ));
    }

    /**
     * Finds subfolders of a folder
     *
     * @param  string $id
     * @return array
     */
    protected function doFindSubFolders($id)
    {
        return iterator_to_array($this->getMongo()->folders->find(array(
            'parent_id' => $id,
        )));
    }
    

    /**
     * Finds all files
     *
     * @return array
     */
    protected function doFindAllFiles()
    {
        return iterator_to_array($this->getMongo()->files->find());
    }
    

    /**
     * Finds a file
     *
     * @param  string     $id
     * @return array|null
     */
    protected function doFindFile($id)
    {
        return $this->getMongo()->files->findOne(array(
            '_id' => new MongoId($id),
        ));
    }
    
    /**
     * Finds a file
     *
     * @param  string $id
     * @return array
     */
    protected function doFindFilesIn($id)
    {
        return iterator_to_array($this->getMongo()->files->find(array(
            'folder_id' => $id,
        )));
    }
    
    /**
     * Uploads a file
     *
     * @param  File   $file
     * @param  Folder $folder
     * @return File
     */
    protected function doUpload(File $file, Folder $folder)
    {
        $document = array(
            'folder_id'     => $folder->getId(),
            'mimetype'      => $file->getMimeType(),
            'size'          => $file->getSize(),
            'name'          => $file->getName(),
            'profile'       => $file->getProfile(),
            'date_uploaded' => new MongoDate($file->getDateUploaded()
                                                  ->getTimestamp()),
        );

        $this->getMongo()->files->ensureIndex(array(
            'folder_id' => 1,
            'name'      => 1,
        ), array(
            'unique' => true,
        ));

        $this->getMongo()->files->insert($document, array('safe' => true));

        $file->setId((string) $document['_id']);
        $file->setFolderId($folder->getId());

        return $file;
    }
    
    /**
     * Creates a folder
     *
     * @param  Folder $folder
     * @return Folder Created folder
     */
    protected function doCreateFolder(Folder $folder)
    {
        $document = $folder->toArray();

        $this->getMongo()->folders->insert($document);
        $this->getMongo()->folders->ensureIndex(array('name' => 1),
                                                array('unique' => true));

        $folder->setId($document['_id']->__toString());

        return $folder;
    }
    

    /**
     * Deletes a folder
     *
     * @param  Folder $folder
     * @return boolean
     */
    protected function doDeleteFolder(Folder $folder)
    {
        $ret = $this->getMongo()->folders->remove(array(
            '_id' => new MongoId($folder->getId()),
        ), array('safe' => true));

        return (boolean) $ret['n'];
    }
    
    /**
     * Deletes a file
     *
     * @param  File    $file
     * @return boolean
     */
    protected function doDeleteFile(File $file)
    {
        $ret = $this->getMongo()->files->remove(array(
            '_id' => new MongoId($file->getId()),
        ), array('safe' => true));

        return (bool) $ret['n'];
    }
    
    /**
     * Updates a folder
     *
     * @param  Folder  $folder
     * @return boolean
     */
    protected function doUpdateFolder(Folder $folder)
    {
        $document = $folder->toArray();

        $this->_filelibToMongo($document);
    	
        $ret = $this->getMongo()->folders->update(array(
            '_id' => new MongoId($folder->getId()),
        ), $document, array('safe' => true));
        
        return (bool) $ret['n'];
    }
    
    /**
     * Updates a file
     *
     * @param  File    $file
     * @return boolean
     */
    protected function doUpdateFile(File $file)
    {
        $document = $file->toArray();

        $this->_filelibToMongo($document);

        $ret = $this->getMongo()->files->update(array(
            '_id' => new MongoId($file->getId()),
        ), $document, array('safe' => true));

        return (bool) $ret['n'];
    }
    

        
    /**
     * Finds the root folder
     *
     * @return array
     */
    protected function doFindRootFolder()
    {
        $mongo = $this->getMongo();

        $folder = $mongo->folders->findOne(array('parent_id' => null));

        if (!$folder) {
            $folder = array(
                'parent_id' => null,
                'name'      => 'root',
                'url'       => '',
            );

            $mongo->folders->save($folder);
        }

        return $folder;
    }
    
    
    /**
     * Finds folder by url
     *
     * @param  string     $url
     * @return array|null
     */
    protected function doFindFolderByUrl($url)
    {
        return $this->getMongo()->folders->findOne(array('url' => $url));
    }
        
    /**
     * @param  Folder     $folder
     * @param  string     $filename
     * @return array|null
     */
    protected function doFindFileByFilename(Folder $folder, $filename)
    {
        return $this->getMongo()->files->findOne(array(
            'folder_id' => $folder->getId(),
            'name'      => $filename,
        ));
    }
    
    /**
     * File to array
     *
     * @param  array $file
     * @return array
     */
    protected function fileToArray($file)
    {
        return array(
            'id'            => (string) $file['_id'],
            'folder_id'     => isset($file['folder_id'])
                                   ? $file['folder_id']
                                   : null,
            'mimetype'      => $file['mimetype'],
            'profile'       => $file['profile'],
            'size'          => (int) $file['size'],
            'name'          => $file['name'],
            'link'          => $file['link'],
            'date_uploaded' => DateTime::createFromFormat(
                                   'U',
                                   $file['date_uploaded']->sec
                               ),
        );
    }

    /**
     * Folder to array
     *
     * @param  array $folder
     * @return array
     */
    protected function folderToArray($folder)
    {
        return array(
            'id'        => (string) $folder['_id'],
            'parent_id' => isset($folder['folder_id'])
                               ? $folder['folder_id']
                               : null,
            'name'      => $folder['name'],
            'url'       => $folder['url']
        );
    }

    /**
     * @param  string           $id
     * @throws FilelibException
     */
    protected function assertValidIdentifier($id)
    {
        if (!is_string($id)) {
            throw new FilelibException('Id must be a string.');
        }
    }

    /**
     * Processes mongo data to fit Filelib requirements
     * 
     * @param array $data
     */
    private function _mongoToFilelib(array &$data)
    {
        $data['id'] = $data['_id']->__toString();
       
        if(isset($data['date_uploaded'])) {
            $data['date_uploaded'] = DateTime::createFromFormat('U', $data['date_uploaded']->sec);    
        }
        
        
        if(isset($data['size'])) {
            $data['size'] = (int) $data['size'];
        }
        
    }
    
    
    /**
     * Processes Filelib data to fit Mongo requirements
     * 
     * @param array $data
     */
    private function _filelibToMongo(array &$data)
    {
       
        
        
        unset($data['id']);
        if(isset($data['date_uploaded'])) {
            $data['date_uploaded'] = new MongoDate($data['date_uploaded']->getTimestamp());    
        }
    }
    
    
    
    
    
    
    
}