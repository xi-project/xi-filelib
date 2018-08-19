<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter;

use ArrayIterator;
use DateTime;
use MongoDB\Model\BSONDocument;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Identifiable;
use Xi\Filelib\Resource\Resource;
use MongoDB\Database;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectID;

/**
 * MongoDB backend for Filelib
 *
 * @author   pekkis
 * @category Xi
 * @package  Filelib
 */
class MongoBackendAdapter implements BackendAdapter
{
    /**
     * MongoDB reference
     *
     * @var Database
     */
    private $mongo;

    /**
     * @var array
     */
    private $finderMap = array(
        'Xi\Filelib\Resource\Resource' => array(
            'id' => '_id',
            'hash' => 'hash',
        ),
        'Xi\Filelib\File\File' => array(
            'id' => '_id',
            'folder_id' => 'folder_id',
            'name' => 'name',
            'uuid' => 'uuid',
        ),
        'Xi\Filelib\Folder\Folder' => array(
            'id' => '_id',
            'parent_id' => 'parent_id',
            'url' => 'url',
        ),
    );

    /**
     * @var array
     */
    private $classNameToResources = array(
        'Xi\Filelib\Resource\Resource' => array('collection' => 'resources', 'exporter' => 'exportResources'),
        'Xi\Filelib\File\File' => array('collection' => 'files', 'exporter' => 'exportFiles'),
        'Xi\Filelib\Folder\Folder' => array('collection' => 'folders', 'exporter' => 'exportFolders'),
    );

    /**
     * @param MongoDB $mongo
     */
    public function __construct(Database $mongo)
    {
        $this->mongo = $mongo;
    }

    public function isOrigin()
    {
        return true;
    }
    
    /**
     * Returns MongoDB
     *
     * @return Database
     */
    public function getMongo()
    {
        return $this->mongo;
    }

    /**
     * @see BackendAdapter::createFile
     */
    public function createFile(File $file, Folder $folder)
    {
        $document = array(
            'folder_id'     => $folder->getId(),
            'name'          => $file->getName(),
            'profile'       => $file->getProfile(),
            'status'        => $file->getStatus(),
            'date_created'  => new UTCDateTime($file->getDateCreated()),
            'uuid'          => $file->getUuid(),
            'resource_id'   => $file->getResource()->getId(),
            'data'      => $file->getData()->toArray(),
        );

        $this->getMongo()->files->
        createIndex(
            array(
                'folder_id' => 1,
                'name'      => 1,
            ),
            array(
                'unique' => true,
            )
        );

        $ret = $this->getMongo()->files->insertOne($document, array('w' => true));

        $file->setId((string) $ret->getInsertedId());
        $file->setFolderId($folder->getId());

        return $file;
    }

    /**
     * @see BackendAdapter::createFolder
     */
    public function createFolder(Folder $folder)
    {
        $document = $folder->toArray();

        $ret = $this->getMongo()->folders->insertOne($document);

        $this
            ->getMongo()
            ->folders
            ->createIndex(
                array('name' => 1),
                array('unique' => true)
            );

        $folder->setId((string) $ret->getInsertedId());

        return $folder;
    }

    /**
     * @see BackendAdapter::deleteFolder
     */
    public function deleteFolder(Folder $folder)
    {
        return $this->deleteIdentifiable($folder);
    }

    /**
     * @see BackendAdapter::deleteFile
     */
    public function deleteFile(File $file)
    {
        return $this->deleteIdentifiable($file);
    }

    /**
     * @see BackendAdapter::updateFolder
     */
    public function updateFolder(Folder $folder)
    {
        $document = $folder->toArray();

        unset($document['id']);

        $ret = $this->getMongo()->folders->updateOne(
            array(
                '_id' => new ObjectId($folder->getId()),
            ),
            [
                '$set' => $document
            ],
            array('w' => true)
        );

        return $ret->isAcknowledged() && (bool) $ret->getModifiedCount();
    }

    public function updateResource(Resource $resource)
    {
        $document = $resource->toArray();

        if ($document['date_created']) {
            $document['date_created'] = new UTCDateTime($resource->getDateCreated());
        }
        unset($document['id']);

        $ret = $this->getMongo()->resources->updateOne(
            array(
                '_id' => new ObjectId($resource->getId()),
            ),
            [
                '$set' => $document
            ],
            array('w' => true)
        );

        return $ret->isAcknowledged() && (bool) $ret->getModifiedCount();
    }

    /**
     * @see BackendAdapter::updateFile
     */
    public function updateFile(File $file)
    {
        $document = $file->toArray();
        $document['resource_id'] = $file->getResource()->getId();

        unset($document['id']);
        unset($document['resource']);

        $document['date_created'] = new UTCDateTime(
            $document['date_created']
        );

        $ret = $this->getMongo()->files->updateOne(
            array(
                '_id' => new ObjectId($file->getId()),
            ),
            [
                '$set' => $document
            ],
            array('w' => true)
        );

        return $ret->isAcknowledged() && (bool) $ret->getModifiedCount();
    }

    /**
     * @see BackendAdapter::createResource
     */
    public function createResource(Resource $resource)
    {
        $document = array(
            'uuid' => $resource->getUuid(),
            'hash' => $resource->getHash(),
            'mimetype' => $resource->getMimetype(),
            'size' => $resource->getSize(),
            'date_created' => new UTCDateTime($resource->getDateCreated()),
            'data' => $resource->getData()->toArray(),
            'exclusive' => $resource->isExclusive(),
        );

        $this->getMongo()->resources->createIndex(
            array(
                'hash' => 1,
            ),
            array(
                'unique' => false,
            )
        );

        $ret = $this->getMongo()->resources->insertOne($document, array('w' => true));
        $resource->setId((string) $ret->getInsertedId());

        return $resource;
    }

    /**
     * @see BackendAdapter::deleteResource
     */
    public function deleteResource(Resource $resource)
    {
        return $this->deleteIdentifiable($resource);
    }

    /**
     * @see BackendAdapter::getNumberOfReferences
     */
    public function getNumberOfReferences(Resource $resource)
    {
        return $this
            ->getMongo()
            ->files
            ->countDocuments(
                array(
                    'resource_id' => $resource->getId(),
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
        $cursor = $this->getMongo()->selectCollection($resources['collection'])->find($params, array('_id'));
        $ret = array();
        foreach ($cursor as $doc) {
            $ret[] = $doc['_id']->__toString();
        }

        return $ret;
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

        array_walk(
            $ids,
            function (&$value) {
                $value = new ObjectId($value);
            }
        );

        $ret = $this->getMongo()->selectCollection($resources['collection'])->find(
            array(
                '_id' => array('$in' => $ids),
            )
        );
        $iter = new ArrayIterator(array());
        foreach ($ret as $doc) {
            $iter->append($doc);
        }

        $exporter = $resources['exporter'];
        return $request->foundMany($this->$exporter($iter));
    }

    /**
     * @param  ArrayIterator $iter
     * @return ArrayIterator
     */
    protected function exportFolders(ArrayIterator $iter)
    {
        $ret = new ArrayIterator(array());

        foreach ($iter as $folder) {

            $ret->append(
                Folder::create(
                    array(
                        'id'        => (string) $folder['_id'],
                        'parent_id' => isset($folder['parent_id']) ? $folder['parent_id'] : null,
                        'name'      => $folder['name'],
                        'url'       => $folder['url'],
                        'uuid'      => $folder['uuid'],
                        'data'      => $folder['data'],
                    )
                )
            );
        }

        return $ret;
    }

    /**
     * @param  ArrayIterator $iter
     * @return ArrayIterator
     */
    protected function exportFiles(ArrayIterator $iter)
    {
        $ret = new ArrayIterator(array());
        $date = new DateTime();

        foreach ($iter as $file) {

            $request = new FindByIdsRequest(array($file['resource_id']), 'Xi\Filelib\Resource\Resource');
            $resource = $this->findByIds($request)->getResult()->first();

            $ret->append(
                File::create(
                    array(
                        'id'            => (string) $file['_id'],
                        'folder_id'     => isset($file['folder_id']) ? $file['folder_id'] : null,
                        'profile'       => $file['profile'],
                        'name'          => $file['name'],
                        'link'          => isset($file['link']) ? $file['link'] : null,
                        'status'        => $file['status'],
                        'date_created'  => $file['date_created']->toDateTime()->setTimezone($date->getTimezone()),
                        'uuid'          => $file['uuid'],
                        'resource'      => $resource,
                        'data'      => $file['data'],
                    )
                )
            );
        }

        return $ret;
    }

    /**
     * @param  Identifiable $identifiable
     * @return bool
     */
    protected function deleteIdentifiable(Identifiable $identifiable)
    {
        $resources = $this->classNameToResources[get_class($identifiable)];
        $ret = $this
            ->getMongo()
            ->selectCollection($resources['collection'])
            ->deleteOne(
                array(
                    '_id' => new ObjectId($identifiable->getId())
                ),
                array('w' => true)
            );

        return $ret->isAcknowledged() && (bool) $ret->getDeletedCount();
    }

    /**
     * @param  ArrayIterator $iter
     * @return ArrayIterator
     */
    protected function exportResources(ArrayIterator $iter)
    {
        $date = new DateTime();
        $ret = new ArrayIterator(array());


        foreach ($iter as $resource) {

            $ret->append(
                Resource::create(
                    array(
                        'id' => (string) $resource['_id'],
                        'uuid' => $resource['uuid'],
                        'hash' => $resource['hash'],
                        'mimetype' => $resource['mimetype'],
                        'size' => $resource['size'],
                        'date_created' => $resource['date_created']->toDateTime()->setTimezone($date->getTimezone()),
                        'data' => $resource['data'],
                        'exclusive' => $resource['exclusive'],
                    )
                )
            );
        }

        return $ret;
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

        if (isset($ret['_id'])) {
            $ret['_id'] = new ObjectId($ret['_id']);
        }

        return $ret;
    }
}
