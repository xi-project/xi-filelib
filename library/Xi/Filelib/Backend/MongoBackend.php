<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\File\File,
    Xi\Filelib\Folder\Folder,
    Xi\Filelib\FilelibException,
    MongoDb,
    MongoId,
    MongoDate,
    DateTime;

/**
 * MongoDB backend for Filelib
 *
 * @author   pekkis
 * @category Xi
 * @package  Filelib
 */
class MongoBackend extends AbstractBackend implements Backend
{
    /**
     * MongoDB reference
     *
     * @var MongoDB
     */
    private $mongo;

    /**
     * @param  MongoDB      $mongo
     * @return MongoBackend
     */
    public function __construct(MongoDB $mongo)
    {
        $this->setMongo($mongo);
    }

    /**
     * Sets MongoDB
     *
     * @param MongoDB $mongo
     */
    public function setMongo(MongoDB $mongo)
    {
        $this->mongo = $mongo;
    }

    /**
     * Returns MongoDB
     *
     * @return MongoDB
     */
    public function getMongo()
    {
        return $this->mongo;
    }

    /**
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
     * @return array
     */
    protected function doFindAllFiles()
    {
        return iterator_to_array($this->getMongo()->files->find());
    }

    /**
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
     * @param  File   $file
     * @param  Folder $folder
     * @return File
     * @throws FilelibException
     */
    protected function doUpload(File $file, Folder $folder)
    {
        if (!$this->findFolder($folder->getId())) {
            throw new FilelibException(sprintf(
                'Folder was not found with id "%s"',
                $folder->getId()
            ));
        }

        $document = array(
            'folder_id'     => $folder->getId(),
            'mimetype'      => $file->getMimeType(),
            'size'          => $file->getSize(),
            'name'          => $file->getName(),
            'profile'       => $file->getProfile(),
            'status'        => $file->getStatus(),
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
     * @param  Folder           $folder
     * @return Folder
     * @throws FilelibException
     */
    protected function doCreateFolder(Folder $folder)
    {
        $document = $folder->toArray();
        $parentId = $document['parent_id'];

        if (isset($parentId) && !$this->findFolder($parentId)) {
            throw new FilelibException(sprintf(
                'Parent folder was not found with id "%s"',
                $parentId
            ));
        }

        $this->getMongo()->folders->insert($document);
        $this->getMongo()->folders->ensureIndex(array('name' => 1),
                                                array('unique' => true));

        $folder->setId($document['_id']->__toString());

        return $folder;
    }

    /**
     * @param  Folder  $folder
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
     * @param  Folder  $folder
     * @return boolean
     */
    protected function doUpdateFolder(Folder $folder)
    {
        $document = $folder->toArray();

        unset($document['id']);

        $ret = $this->getMongo()->folders->update(array(
            '_id' => new MongoId($folder->getId()),
        ), $document, array('safe' => true));

        return (bool) $ret['n'];
    }

    /**
     * @param  File             $file
     * @return boolean
     * @throws FilelibException
     */
    protected function doUpdateFile(File $file)
    {
        $document = $file->toArray();

        unset($document['id']);

        if (!$this->findFolder($document['folder_id'])) {
            throw new FilelibException(sprintf(
                'Folder was not found with id "%s"',
                $document['folder_id']
            ));
        }

        $document['date_uploaded'] = new MongoDate(
            $document['date_uploaded']->getTimestamp()
        );

        $ret = $this->getMongo()->files->update(array(
            '_id' => new MongoId($file->getId()),
        ), $document, array('safe' => true));

        return (bool) $ret['n'];
    }

    /**
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
     * @param  array $file
     * @return array
     */
    protected function fileToArray($file)
    {
        $date = new DateTime();

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
            'status'        => $file['status'],
            'date_uploaded' => DateTime::createFromFormat(
                                   'U',
                                   $file['date_uploaded']->sec
                               )->setTimezone($date->getTimezone()),
        );
    }

    /**
     * @param  array $folder
     * @return array
     */
    protected function folderToArray($folder)
    {
        return array(
            'id'        => (string) $folder['_id'],
            'parent_id' => isset($folder['parent_id'])
                               ? $folder['parent_id']
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
}
