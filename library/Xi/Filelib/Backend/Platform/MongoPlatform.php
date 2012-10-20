<?php

namespace Xi\Filelib\Backend\Platform;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Exception\NonUniqueFileException;
use MongoDb;
use MongoId;
use MongoDate;
use MongoCursorException;
use DateTime;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * MongoDB backend for Filelib
 *
 * @author   pekkis
 * @category Xi
 * @package  Filelib
 */
class MongoPlatform extends AbstractPlatform implements Platform
{
    /**
     * MongoDB reference
     *
     * @var MongoDB
     */
    private $mongo;

    /**
     * @param  EventDispatcherInterface $eventDispatcher
     * @param  MongoDB      $mongo
     * @return MongoPlatform
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, MongoDB $mongo)
    {
        parent::__construct($eventDispatcher);
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
     * @see AbstractPlatform::doFindFolder
     */
    protected function doFindFolder($id)
    {
        return $this->getMongo()->folders->findOne(array(
            '_id' => new MongoId($id),
        ));
    }

    /**
     * @see AbstractPlatform::doFindSubFolders
     */
    protected function doFindSubFolders($id)
    {
        return iterator_to_array($this->getMongo()->folders->find(array(
            'parent_id' => $id,
        )));
    }

    /**
     * @see AbstractPlatform::doFindAllFiles
     */
    protected function doFindAllFiles()
    {
        return iterator_to_array($this->getMongo()->files->find());
    }

    /**
     * @see AbstractPlatform::doFindFile
     */
    protected function doFindFile($id)
    {
        return $this->getMongo()->files->findOne(array(
            '_id' => new MongoId($id),
        ));
    }

    /**
     * @see AbstractPlatform::doFindFiles
     */
    protected function doFindFilesIn($id)
    {
        return iterator_to_array($this->getMongo()->files->find(array(
            'folder_id' => $id,
        )));
    }

    /**
     * @see AbstractPlatform::doUpload
     */
    protected function doUpload(File $file, Folder $folder)
    {
        $document = array(
            'folder_id'     => $folder->getId(),
            'name'          => $file->getName(),
            'profile'       => $file->getProfile(),
            'status'        => $file->getStatus(),
            'date_created'  => new MongoDate($file->getDateCreated()->getTimestamp()),
            'uuid'          => $file->getUuid(),
            'resource_id'   => $file->getResource()->getId(),
            'versions'      => $file->getVersions(),
        );

        $this->getMongo()->files->ensureIndex(array(
            'folder_id' => 1,
            'name'      => 1,
        ), array(
            'unique' => true,
        ));

        try {
            $this->getMongo()->files->insert($document, array('safe' => true));
        } catch (MongoCursorException $e) {
            $this->throwNonUniqueFileException($file, $folder);
        }

        $file->setId((string) $document['_id']);
        $file->setFolderId($folder->getId());

        return $file;
    }

    /**
     * @see AbstractPlatform::doCreateFolder
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
     * @see AbstractPlatform::doDeleteFolder
     */
    protected function doDeleteFolder(Folder $folder)
    {
        $ret = $this->getMongo()->folders->remove(array(
            '_id' => new MongoId($folder->getId()),
        ), array('safe' => true));

        return (boolean) $ret['n'];
    }

    /**
     * @see AbstractPlatform::doDeleteFile
     */
    protected function doDeleteFile(File $file)
    {
        $ret = $this->getMongo()->files->remove(array(
            '_id' => new MongoId($file->getId()),
        ), array('safe' => true));

        return (bool) $ret['n'];
    }

    /**
     * @see AbstractPlatform::doUpdateFolder
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
     * @see AbstractPlatform::doUpdateResource
     */
    protected function doUpdateResource(Resource $resource)
    {
        $document = $resource->toArray();

        if ($document['date_created']) {
            $document['date_created'] = new MongoDate($resource->getDateCreated()->getTimestamp());
        }
        unset($document['id']);

        $ret = $this->getMongo()->resources->update(array(
            '_id' => new MongoId($resource->getId()),
        ), $document, array('safe' => true));

        return (bool) $ret['n'];
    }



    /**
     * @see AbstractPlatform::doUpdateFile
     */
    protected function doUpdateFile(File $file)
    {
        $document = $file->toArray();

        $document['resource_id'] = $file->getResource()->getId();

        unset($document['id']);
        unset($document['resource']);

        $document['date_created'] = new MongoDate(
            $document['date_created']->getTimestamp()
        );

        $ret = $this->getMongo()->files->update(array(
            '_id' => new MongoId($file->getId()),
        ), $document, array('safe' => true));

        return (bool) $ret['n'];
    }

    /**
     * @see AbstractPlatform::doFindRootFolder
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
                'uuid'      => $this->generateUuid(),
            );

            $mongo->folders->save($folder);
        }

        return $folder;
    }

    /**
     * @see AbstractPlatform::doFindFolderByUrl
     */
    protected function doFindFolderByUrl($url)
    {
        return $this->getMongo()->folders->findOne(array('url' => $url));
    }

    /**
     * @see AbstractPlatform::doFindByFilename
     */
    protected function doFindFileByFilename(Folder $folder, $filename)
    {
        return $this->getMongo()->files->findOne(array(
            'folder_id' => $folder->getId(),
            'name'      => $filename,
        ));
    }

    /**
     * @see AbstractPlatform::fileToArray
     */
    protected function fileToArray($file)
    {
        $date = new DateTime();

        return array(
            'id'            => (string) $file['_id'],
            'folder_id'     => isset($file['folder_id'])
                                   ? $file['folder_id']
                                   : null,
            'profile'       => $file['profile'],
            'name'          => $file['name'],
            'link'          => isset($file['link']) ? $file['link'] : null,
            'status'        => $file['status'],
            'date_created'  => DateTime::createFromFormat('U', $file['date_created']->sec)->setTimezone($date->getTimezone()),
            'uuid'          => $file['uuid'],
            'resource'      => $this->resourceToArray($this->doFindResource($file['resource_id'])),
            'versions'      => $file['versions'],
        );
    }

    /**
     * @see AbstractPlatform::folderToArray
     */
    protected function folderToArray($folder)
    {
        return array(
            'id'        => (string) $folder['_id'],
            'parent_id' => isset($folder['parent_id'])
                               ? $folder['parent_id']
                               : null,
            'name'      => $folder['name'],
            'url'       => $folder['url'],
            'uuid'      => $folder['uuid'],
        );
    }

    /**
     * @see AbstractPlatform::isValidIdentifier
     */
    protected function isValidIdentifier($id)
    {
        return is_string($id);
    }

    /**
     * @see AbstractPlatform::doFindResource
     */
    protected function doFindResource($id)
    {
        return $this->getMongo()->resources->findOne(array(
            '_id' => new MongoId($id),
        ));
    }

    /**
     * @see AbstractPlatform::doFindResourcesByHash
     */
    protected function doFindResourcesByHash($hash)
    {
        return iterator_to_array($this->getMongo()->resources->find(array(
            'hash' => $hash,
        )));
    }

    /**
     * @see AbstractPlatform::doCreateResource
     */
    protected function doCreateResource(Resource $resource)
    {
        $document = array(
            'hash' => $resource->getHash(),
            'mimetype' => $resource->getMimetype(),
            'size' => $resource->getSize(),
            'date_created' => new MongoDate($resource->getDateCreated()->getTimestamp()),
            'versions' => $resource->getVersions(),
            'exclusive' => $resource->isExclusive(),
        );

        $this->getMongo()->resources->ensureIndex(array(
            'hash' => 1,
        ), array(
            'unique' => false,
        ));

        $this->getMongo()->resources->insert($document, array('safe' => true));
        $resource->setId((string) $document['_id']);

        return $resource;
    }

    /**
     * @see AbstractPlatform::doDeleteResource
     */
    protected function doDeleteResource(Resource $resource)
    {
        $ret = $this->getMongo()->resources->remove(array('_id' => new MongoId($resource->getId())), array('safe' => true));
        return (boolean) $ret['n'];
    }

    /**
     * @see AbstractPlatform::resourceToArray
     */
    protected function resourceToArray($resource)
    {
        $date = new DateTime();
        return Resource::create(array(
            'id' => (string) $resource['_id'],
            'hash' => $resource['hash'],
            'mimetype' => $resource['mimetype'],
            'size' => $resource['size'],
            'date_created' => DateTime::createFromFormat('U', $resource['date_created']->sec)->setTimezone($date->getTimezone()),
            'versions' => $resource['versions'],
            'exclusive' => $resource['exclusive'],
        ));
    }

    /**
     * @see AbstractPlatform::doGetNumberOfReferences
     */
    protected function doGetNumberOfReferences(Resource $resource)
    {
        $refs = $this->getMongo()->files->find(array(
            'resource_id' => $resource->getId(),
        ));

        return $refs->count();
    }

}
