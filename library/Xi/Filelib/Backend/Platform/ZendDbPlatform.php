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
use Xi\Filelib\Backend\Platform\ZendDb\FileTable;
use Xi\Filelib\Backend\Platform\ZendDb\FolderTable;
use Xi\Filelib\Backend\Platform\ZendDb\ResourceTable;
use Zend_Db_Adapter_Abstract;
use Zend_Db_Table_Abstract;
use Zend_Db_Statement_Exception;
use DateTime;
use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\IdentityMap\Identifiable;
use Iterator;
use ArrayIterator;


/**
 * ZendDb backend for filelib
 *
 * @author   pekkis
 * @category Xi
 * @package  Filelib
 */
class ZendDbPlatform extends AbstractPlatform implements Platform
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    private $db;

    /**
     * @var Zend_Db_Table_Abstract
     */
    private $fileTable;

    /**
     * @var Zend_Db_Table_Abstract
     */
    private $folderTable;

    /**
     * @var Zend_Db_Table_Abstract
     */
    private $resourceTable;

    private $finderMap = array(
        'Xi\Filelib\File\Resource' => array(
            'id' => 'id',
            'hash' => 'hash',
        ),
        'Xi\Filelib\File\File' => array(
            'id' => 'id',
            'folder_id' => 'folder_id',
            'name' => 'filename',
        ),
        'Xi\Filelib\Folder\Folder' => array(
            'id' => 'id',
            'parent_id' => 'parent_id',
        ),
    );

    private $classNameToResources;

    /**
     * @param  EventDispatcherInterface $eventDispatcher
     * @param  Zend_Db_Adapter_Abstract $db
     * @return ZendDbPlatform
     */
    public function __construct(Zend_Db_Adapter_Abstract $db)
    {
        $this->setDb($db);
        $this->classNameToResources = array(
            'Xi\Filelib\File\Resource' => array('table' => array($this, 'getResourceTable'), 'exporter' => 'exportResources'),
            'Xi\Filelib\File\File' => array('table' => array($this, 'getFileTable'), 'exporter' => 'exportFiles'),
            'Xi\Filelib\Folder\Folder' => array('table' => array($this, 'getFolderTable'), 'exporter' => 'exportFolders'),
        );

    }

    /**
     * Sets db adapter
     *
     * @param Zend_Db_Adapter_Abstract $db
     */
    public function setDb(Zend_Db_Adapter_Abstract $db)
    {
        $this->db = $db;
    }

    /**
     * Returns db adapter
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Returns file table
     *
     * @return FileTable
     */
    public function getFileTable()
    {
        if (!$this->fileTable) {
            $this->fileTable = new FileTable($this->getDb());
        }
        return $this->fileTable;
    }

    /**
     * Returns folder table
     *
     * @return FolderTable
     */
    public function getFolderTable()
    {
        if (!$this->folderTable) {
            $this->folderTable = new FolderTable($this->getDb());
        }

        return $this->folderTable;
    }

    /**
     * @param  Zend_Db_Table_Abstract $folderTable
     * @return ZendDbPlatform
     */
    public function setFolderTable(Zend_Db_Table_Abstract $folderTable)
    {
        $this->folderTable = $folderTable;

        return $this;
    }

    /**
     * Returns resource table
     *
     * @return ResourceTable
     */
    public function getResourceTable()
    {
        if (!$this->resourceTable) {
            $this->resourceTable = new ResourceTable($this->getDb());
        }
        return $this->resourceTable;
    }


    /**
     * @param  Zend_Db_Table_Abstract $resourceTable
     * @return ZendDbPlatform
     */
    public function setResourceTable(Zend_Db_Table_Abstract $resourceTable)
    {
        $this->resourceTable = $resourceTable;
        return $this;
    }

    /**
     * @see AbstractPlatform::doCreateFolder
     */
    public function createFolder(Folder $folder)
    {
        $folderRow = $this->getFolderTable()->createRow();
        $folderRow->foldername = $folder->getName();
        $folderRow->parent_id  = $folder->getParentId();
        $folderRow->folderurl  = $folder->getUrl();
        $folderRow->uuid = $folder->getUuid();

        $folderRow->save();

        $folder->setId((int) $folderRow->id);

        return $folder;
    }

    /**
     * @see AbstractPlatform::doDeleteFolder
     */
    public function deleteFolder(Folder $folder)
    {
        return (bool) $this->getFolderTable()->delete(
            $this->getFolderTable()
                 ->getAdapter()
                 ->quoteInto("id = ?", $folder->getId())
        );
    }

    /**
     * @see AbstractPlatform::doUpdateFolder
     */
    public function updateFolder(Folder $folder)
    {
        return (bool) $this->getFolderTable()->update(
            array(
                'id'         => $folder->getId(),
                'parent_id'  => $folder->getParentId(),
                'foldername' => $folder->getName(),
                'folderurl'  => $folder->getUrl(),
                'uuid'       => $folder->getUuid(),
            ),
            $this->getFolderTable()
                 ->getAdapter()
                 ->quoteInto('id = ?', $folder->getId())
        );
    }

    /**
     * @see AbstractPlatform::doUpdateResource
     */
    public function updateResource(Resource $resource)
    {
        return (bool) $this->getResourceTable()->update(
            array(
                'versions' => serialize($resource->getVersions()),
                'exclusive' => $resource->isExclusive() ? 1 : 0,
            ),
            $this->getResourceTable()
                 ->getAdapter()
                 ->quoteInto('id = ?', $resource->getId())
        );
    }

    /**
     * @see AbstractPlatform::doUpdateFile
     */
    public function updateFile(File $file)
    {
        $data = $file->toArray();

        return (bool) $this->getFileTable()->update(
            array(
                'folder_id'     => $data['folder_id'],
                'filename'      => $data['name'],
                'fileprofile'   => $data['profile'],
                'date_created' => $data['date_created']->format('Y-m-d H:i:s'),
                'filelink'      => $data['link'],
                'status'        => $data['status'],
                'uuid'          => $data['uuid'],
                'resource_id'   => $data['resource']->getId(),
                'versions' => serialize($data['versions']),
            ),
            $this->getFileTable()
                 ->getAdapter()
                 ->quoteInto('id = ?', $data['id'])
        );
    }

    /**
     * @see AbstractPlatform::doDeleteFile
     */
    public function deleteFile(File $file)
    {
        $fileRow = $this->getFileTable()->find($file->getId())->current();

        if (!$fileRow) {
            return false;
        }

        $fileRow->delete();

        return true;
    }

    /**
     * @see AbstractPlatform::doUpload
     */
    public function createFile(File $file, Folder $folder)
    {
        $row = $this->getFileTable()->createRow();

        $row->folder_id     = $folder->getId();
        $row->filename      = $file->getName();
        $row->fileprofile   = $file->getProfile();
        $row->date_created = $file->getDateCreated()->format('Y-m-d H:i:s');
        $row->status        = $file->getStatus();
        $row->uuid          = $file->getUuid();
        $row->resource_id   = $file->getResource()->getId();
        $row->versions = serialize($file->getVersions());

        try {
            $row->save();
        } catch (Zend_Db_Statement_Exception $e) {
            $this->throwNonUniqueFileException($file, $folder);
        }

        $file->setId((int) $row->id);
        $file->setFolderId($row->folder_id);

        return $file;
    }

    protected function exportFiles(Iterator $iter)
    {
        $ret = new ArrayIterator(array());
        foreach ($iter as $fileRow) {
            $resource = $this->findByIds(array($fileRow['resource_id']), 'Xi\Filelib\File\Resource')->current();

            $ret->append(File::create(array(
                'id'            => $fileRow['id'],
                'folder_id'     => $fileRow['folder_id'],
                'name'          => $fileRow['filename'],
                'profile'       => $fileRow['fileprofile'],
                'link'          => $fileRow['filelink'],
                'date_created' => new DateTime($fileRow['date_created']),
                'status'        => (int) $fileRow['status'],
                'uuid'          => $fileRow['uuid'],
                'resource'      => $resource,
                'versions'      => unserialize($fileRow['versions']),
            )));
        }
        return $ret;
    }


    /**
     * @see AbstractPlatform::exportFolder
     */
    protected function exportFolders(Iterator $iter)
    {
        $ret = new ArrayIterator(array());
        foreach ($iter as $folder) {

            $ret->append(Folder::create(array(
                'id'        => (int) $folder['id'],
                'parent_id' => $folder['parent_id'] ? (int) $folder['parent_id'] : null,
                'name'      => $folder['foldername'],
                'url'       => $folder['folderurl'],
                'uuid'      => $folder['uuid'],
            )));
        }

        return $ret;
    }

    protected function exportResources(Iterator $iter)
    {
        $ret = new ArrayIterator(array());
        foreach ($iter as $row) {
            $ret->append(Resource::create(array(
                'id' => (int) $row['id'],
                'hash' => $row['hash'],
                'size' => (int) $row['filesize'],
                'mimetype' => $row['mimetype'],
                'date_created' => new DateTime($row['date_created']),
                'versions' => unserialize($row['versions']),
                'exclusive' => (bool) $row['exclusive'],
            )));
        }
        return $ret;
    }

    /**
     * @see AbstractPlatform::doCreateResource
     */
    public function createResource(Resource $resource)
    {
        $row = $this->getResourceTable()->createRow();
        $row->hash = $resource->getHash();
        $row->mimetype = $resource->getMimetype();
        $row->filesize = $resource->getSize();
        $row->date_created  = $resource->getDateCreated()->format('Y-m-d H:i:s');
        $row->versions = serialize($resource->getVersions());
        $row->exclusive = $resource->isExclusive() ? 1 : 0;
        $row->save();

        $resource->setId((int) $row->id);
        return $resource;
    }

    /**
     * @see AbstractPlatform::doDeleteResource
     */
    public function deleteResource(Resource $resource)
    {
        $row = $this->getResourceTable()->find($resource->getId())->current();
        if (!$row) {
            return false;
        }
        $row->delete();
        return true;
    }


    /**
     * @see AbstractPlatform::doGetNumberOfReferences
     */
    public function getNumberOfReferences(Resource $resource)
    {
        return $this->db->fetchOne("SELECT COUNT(id) FROM xi_filelib_file WHERE resource_id = ?", array($resource->getId()));
    }


    public function assertValidIdentifier(Identifiable $identifiable)
    {
        return is_numeric($identifiable->getId());
    }


    public function findByFinder(Finder $finder)
    {
        $resources = $this->classNameToResources[$finder->getResultClass()];
        $params = $this->finderParametersToInternalParameters($finder);

        $table = call_user_func($resources['table']);
        $tableName = $table->info('name');

        $select = $table->getAdapter()->select();

        $select->from($tableName, 'id');

        foreach ($params as $key => $param) {

            if ($param === null) {
                $select->where("{$key} IS NULL");
            } else {
                $select->where("{$key} = ?", $param);
            }
        }

        $ids = $table->getAdapter()->fetchCol($select);
        return $ids;
    }

    private function finderParametersToInternalParameters(Finder $finder)
    {
        $ret = array();
        foreach ($finder->getParameters() as $key => $value) {
            $ret[$this->finderMap[$finder->getResultClass()][$key]] = $value;
        }
        return $ret;
    }

    public function findByIds(array $ids, $className)
    {
        if (!$ids) {
            return new ArrayIterator(array());
        }

        $resources = $this->classNameToResources[$className];

        $table = call_user_func($resources['table']);

        $rows = $table->find($ids);

        $exporter = $resources['exporter'];
        return $this->$exporter($rows);
    }


}
