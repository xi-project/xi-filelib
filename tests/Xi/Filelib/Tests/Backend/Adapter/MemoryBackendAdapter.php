<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Backend\Adapter;

use ArrayIterator;
use DateTime;
use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Backend\Adapter\BackendAdapter;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Resource\ConcreteResource;

/**
 * Memory backend adapter
 *
 * @author pekkis
 */
class MemoryBackendAdapter implements BackendAdapter
{
    private $data = array(
        'resources' => array(),
        'files' => array(),
        'folders' => array(),
    );

    /**
     * @var array
     */
    private $finderMap = array(
        'Xi\Filelib\Resource\ConcreteResource' => array(
            'id' => 'id',
            'hash' => 'hash',
        ),
        'Xi\Filelib\File\File' => array(
            'id' => 'id',
            'folder_id' => 'folder_id',
            'name' => 'name',
            'uuid' => 'uuid',
        ),
        'Xi\Filelib\Folder\Folder' => array(
            'id' => 'id',
            'parent_id' => 'parent_id',
            'url' => 'url',
        ),
    );

    /**
     * @var array
     */
    private $classNameToResources = array(
        'Xi\Filelib\Resource\ConcreteResource' => array('collection' => 'resources', 'exporter' => 'exportResources'),
        'Xi\Filelib\File\File' => array('collection' => 'files', 'exporter' => 'exportFiles'),
        'Xi\Filelib\Folder\Folder' => array('collection' => 'folders', 'exporter' => 'exportFolders'),
    );

    public function isOrigin()
    {
        return true;
    }

    protected function createId()
    {
        return Uuid::uuid4()->toString();
    }

    private function remove($what, $id)
    {
        if (isset($this->data[$what][$id])) {
            unset($this->data[$what][$id]);
            return true;
        }
        return false;
    }

    private function create($what, $id, $doc)
    {
        $this->data[$what][$id] = $doc;
    }

    private function update($what, $id, $doc)
    {

        if (!isset($this->data[$what][$id])) {
            return false;
        }

        $this->data[$what][$id] = $doc;
        return true;
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
            'date_created'  => $file->getDateCreated()->format('Y-m-d H:i:s'),
            'uuid'          => $file->getUuid(),
            'resource_id'   => $file->getResource()->getId(),
            'data'      => $file->getData()->toArray(),
        );

        $document['id'] = $this->createId();
        $this->create('files', $document['id'], $document);

        $file->setId($document['id']);
        $file->setFolderId($folder->getId());

        return $file;
    }

    /**
     * @see BackendAdapter::createFolder
     */
    public function createFolder(Folder $folder)
    {
        $document = $folder->toArray();
        $document['id'] = $this->createId();
        $this->create('folders', $document['id'], $document);
        $folder->setId($document['id']);

        return $folder;
    }

    /**
     * @see BackendAdapter::deleteFolder
     */
    public function deleteFolder(Folder $folder)
    {
        return $this->remove('folders', $folder->getId());

    }

    /**
     * @see BackendAdapter::deleteFile
     */
    public function deleteFile(File $file)
    {
        return $this->remove('files', $file->getId());
    }

    /**
     * @see BackendAdapter::updateFolder
     */
    public function updateFolder(Folder $folder)
    {
        $document = $folder->toArray();
        return $this->update('folders', $folder->getId(), $document);
    }

    public function updateResource(ConcreteResource $resource)
    {
        $document = $resource->toArray();

        if ($document['date_created']) {
            $document['date_created'] = $document['date_created']->format('Y-m-d H:i:s');
        }

        return $this->update('resources', $resource->getId(), $document);
    }

    /**
     * @see BackendAdapter::updateFile
     */
    public function updateFile(File $file)
    {
        $document = $file->toArray();
        $document['resource_id'] = $file->getResource()->getId();
        $document['date_created'] = $document['date_created']->format('Y-m-d H:i:s');
        unset($document['resource']);

        $this->update('files', $file->getId(), $document);

        return true;
    }

    /**
     * @see BackendAdapter::createResource
     */
    public function createResource(ConcreteResource $resource)
    {
        $document = array(
            'uuid' => $resource->getUuid(),
            'hash' => $resource->getHash(),
            'mimetype' => $resource->getMimetype(),
            'size' => $resource->getSize(),
            'date_created' => $resource->getDateCreated()->format('Y-m-d H:i:s'),
            'data' => $resource->getData()->toArray(),
            'exclusive' => $resource->isExclusive(),
        );

        $document['id'] = $this->createId();
        $this->create('resources', $document['id'], $document);

        $resource->setId($document['id']);

        return $resource;
    }

    /**
     * @see BackendAdapter::deleteResource
     */
    public function deleteResource(ConcreteResource $resource)
    {
        $this->remove('resources', $resource->getId());
    }

    /**
     * @see BackendAdapter::getNumberOfReferences
     */
    public function getNumberOfReferences(ConcreteResource $resource)
    {
        $count = 0;
        foreach ($this->data['files'] as $file) {
            if ($file['resource_id'] == $resource->getId()) {
                $count = $count + 1;
            }
        }
        return $count;
    }

    /**
     * @see BackendAdapter::findByFinder
     */
    public function findByFinder(Finder $finder)
    {
        $resources = $this->classNameToResources[$finder->getResultClass()];
        $params = $this->finderParametersToInternalParameters($finder);

        $all = $this->data[$resources['collection']];

        $ret = array();
        foreach ($all as $obj) {

            $found = true;

            foreach ($params as $key => $param) {
                if ($obj[$key] !== $param) {
                    $found = false;
                }
            }

            if ($found) {
                $ret[] = $obj['id'];
            }

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

        $all = $this->data[$resources['collection']];

        $iter = new ArrayIterator(array());

        foreach ($all as $obj) {
            if (in_array($obj['id'], $ids)) {
                $iter->append($obj);
            }
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
                        'id'        => (string) $folder['id'],
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

            $request = new FindByIdsRequest(array($file['resource_id']), 'Xi\Filelib\Resource\ConcreteResource');

            $resource = $this->findByIds($request)->getResult()->first();

            $ret->append(
                File::create(
                    array(
                        'id'            => (string) $file['id'],
                        'folder_id'     => isset($file['folder_id']) ? $file['folder_id'] : null,
                        'profile'       => $file['profile'],
                        'name'          => $file['name'],
                        'link'          => isset($file['link']) ? $file['link'] : null,
                        'status'        => $file['status'],
                        'date_created'  => DateTime::createFromFormat(
                            'Y-m-d H:i:s',
                            $file['date_created']
                        )->setTimezone($date->getTimezone()),
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
     * @param  ArrayIterator $iter
     * @return ArrayIterator
     */
    protected function exportResources(ArrayIterator $iter)
    {
        $date = new DateTime();
        $ret = new ArrayIterator(array());

        foreach ($iter as $resource) {
            $ret->append(
                ConcreteResource::create(
                    array(
                        'id' => (string) $resource['id'],
                        'uuid' => $resource['uuid'],
                        'hash' => $resource['hash'],
                        'mimetype' => $resource['mimetype'],
                        'size' => $resource['size'],
                        'date_created' => DateTime::createFromFormat(
                            'Y-m-d H:i:s',
                            $resource['date_created']
                        )->setTimezone($date->getTimezone()),
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
        return $ret;
    }
}
