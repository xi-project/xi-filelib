<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Platform;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Exception\NonUniqueFileException;

use Xi\Filelib\IdentityMap\Identifiable;

use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\Backend\Finder\ResourceFinder;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\Backend\Finder\FolderFinder;

use MongoDb;
use MongoId;
use MongoDate;
use MongoCursorException;
use DateTime;
use ArrayIterator;

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

    private $finderMap = array(
        'Xi\Filelib\File\Resource' => array(
            'id' => '_id',
            'hash' => 'hash',
        ),
        'Xi\Filelib\File\File' => array(
            'id' => '_id',
            'folder_id' => 'folder_id',
            'name' => 'name',
        ),
        'Xi\Filelib\Folder\Folder' => array(
            'id' => '_id',
            'parent_id' => 'parent_id',
            'url' => 'url',
        ),
    );

    private $classNameToResources = array(
        'Xi\Filelib\File\Resource' => array('collection' => 'resources', 'exporter' => 'exportResources'),
        'Xi\Filelib\File\File' => array('collection' => 'files', 'exporter' => 'exportFiles'),
        'Xi\Filelib\Folder\Folder' => array('collection' => 'folders', 'exporter' => 'exportFolders'),
    );


    /**
     * @param  MongoDB      $mongo
     * @return MongoPlatform
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

    public function createFile(File $file, Folder $folder)
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

    public function createFolder(Folder $folder)
    {
        $document = $folder->toArray();

        $this->getMongo()->folders->insert($document);
        $this->getMongo()->folders->ensureIndex(array('name' => 1),
                                                array('unique' => true));

        $folder->setId($document['_id']->__toString());

        return $folder;
    }

    public function deleteFolder(Folder $folder)
    {
        return $this->deleteIdentifiable($folder);
    }

    public function deleteFile(File $file)
    {
        return $this->deleteIdentifiable($file);
    }

    public function updateFolder(Folder $folder)
    {
        $document = $folder->toArray();

        unset($document['id']);

        $ret = $this->getMongo()->folders->update(array(
            '_id' => new MongoId($folder->getId()),
        ), $document, array('safe' => true));

        return (bool) $ret['n'];
    }

    public function updateResource(Resource $resource)
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

    public function updateFile(File $file)
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
     * @see AbstractPlatform::exportFolder
     */
    public function exportFolders(ArrayIterator $iter)
    {
        $ret = new ArrayIterator(array());

        foreach ($iter as $folder) {
            $ret->append(Folder::create(array(
                'id'        => (string) $folder['_id'],
                'parent_id' => isset($folder['parent_id']) ? $folder['parent_id'] : null,
                'name'      => $folder['name'],
                'url'       => $folder['url'],
                'uuid'      => $folder['uuid'],
            )));
        }
        return $ret;
    }

    /**
     * @see AbstractPlatform::exportFile
     */
    public function exportFiles(ArrayIterator $iter)
    {
        $ret = new ArrayIterator(array());
        $date = new DateTime();

        foreach ($iter as $file) {

            $resource = $this->findByIds(array($file['resource_id']), 'Xi\Filelib\File\Resource')->current();

            $ret->append(File::create(array(
                'id'            => (string) $file['_id'],
                'folder_id'     => isset($file['folder_id']) ? $file['folder_id'] : null,
                'profile'       => $file['profile'],
                'name'          => $file['name'],
                'link'          => isset($file['link']) ? $file['link'] : null,
                'status'        => $file['status'],
                'date_created'  => DateTime::createFromFormat('U', $file['date_created']->sec)->setTimezone($date->getTimezone()),
                'uuid'          => $file['uuid'],
                'resource'      => $resource,
                'versions'      => $file['versions'],
            )));
        }
        return $ret;
    }

    /**
     * @see AbstractPlatform::isValidIdentifier
     */
    public function isValidIdentifier($id)
    {
        return is_string($id);
    }

    public function createResource(Resource $resource)
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


    protected function deleteIdentifiable(Identifiable $identifiable)
    {
        $resources = $this->classNameToResources[get_class($identifiable)];
        $ret = $this->getMongo()->selectCollection($resources['collection'])->remove(array('_id' => new MongoId($identifiable->getId())), array('safe' => true));
        return (boolean) $ret['n'];
    }


    public function deleteResource(Resource $resource)
    {
       return $this->deleteIdentifiable($resource);
    }

    /**
     * @see AbstractPlatform::exportResource
     */
    public function exportResources(ArrayIterator $iter)
    {
        $date = new DateTime();
        $ret = new ArrayIterator(array());

        foreach ($iter as $resource) {
            $ret->append(Resource::create(array(
                'id' => (string) $resource['_id'],
                'hash' => $resource['hash'],
                'mimetype' => $resource['mimetype'],
                'size' => $resource['size'],
                'date_created' => DateTime::createFromFormat('U', $resource['date_created']->sec)->setTimezone($date->getTimezone()),
                'versions' => $resource['versions'],
                'exclusive' => $resource['exclusive'],
            )));
        }
        return $ret;
    }

    public function getNumberOfReferences(Resource $resource)
    {
        $refs = $this->getMongo()->files->find(array(
            'resource_id' => $resource->getId(),
        ));

        return $refs->count();
    }

    public function assertValidIdentifier(Identifiable $identifiable)
    {
        return is_string($identifiable->getId());
    }


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

    private function finderParametersToInternalParameters(Finder $finder)
    {
        $ret = array();
        foreach ($finder->getParameters() as $key => $value) {
            $ret[$this->finderMap[$finder->getResultClass()][$key]] = $value;
        }

        if (isset($ret['_id'])) {
            $ret['_id'] = new MongoId($ret['_id']);
        }

        return $ret;
    }





    public function findByIds(array $ids, $className)
    {
        if (!$ids) {
            return new ArrayIterator(array());
        }

        $resources = $this->classNameToResources[$className];

        array_walk($ids, function(&$value) {
            $value = new MongoId($value);
        });

        $ret = $this->getMongo()->selectCollection($resources['collection'])->find(array(
            '_id' => array('$in' => $ids),
        ));

        $iter = new ArrayIterator(array());

        foreach($ret as $doc) {
            $iter->append($doc);
        }

        $exporter = $resources['exporter'];
        return $this->$exporter($iter);
    }



}
