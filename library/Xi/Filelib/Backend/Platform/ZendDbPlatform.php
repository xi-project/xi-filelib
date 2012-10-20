<?php

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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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

    /**
     * @param  EventDispatcherInterface $eventDispatcher
     * @param  Zend_Db_Adapter_Abstract $db
     * @return ZendDbPlatform
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Zend_Db_Adapter_Abstract $db)
    {
        parent::__construct($eventDispatcher);
        $this->setDb($db);
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
     * @see AbstractPlatform::doFindRootFolder
     */
    protected function doFindRootFolder()
    {
        $folder = $this->getFolderTable()->fetchRow(array('parent_id IS NULL'));

        if (!$folder) {
            $folder = $this->getFolderTable()->createRow();

            $folder->foldername = 'root';
            $folder->parent_id  = null;
            $folder->folderurl  = '';
            $folder->uuid = $this->generateUuid();

            $folder->save();
        }

        return $folder;
    }

    /**
     * @see AbstractPlatform::doFindFolder
     */
    protected function doFindFolder($id)
    {
        return $this->getFolderTable()->find($id)->current();
    }

    /**
     * @see AbstractPlatform::doCreateFolder
     */
    protected function doCreateFolder(Folder $folder)
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
    protected function doDeleteFolder(Folder $folder)
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
    protected function doUpdateFolder(Folder $folder)
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
    protected function doUpdateResource(Resource $resource)
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
     * @see AbstractPlatform::doFindSubFolders
     */
    protected function doFindSubFolders($id)
    {
        return $this->getFolderTable()->fetchAll(array(
            'parent_id = ?' => $id,
        ))->toArray();
    }

    /**
     * @see AbstractPlatform::doFindFolderByUrl
     */
    protected function doFindFolderByUrl($url)
    {
        return $this->getFolderTable()->fetchRow(array(
            'folderurl = ?' => $url,
        ));
    }

    /**
     * @see AbstractPlatform::doFindFilesIn
     */
    protected function doFindFilesIn($id)
    {
        return $this->getFileTable()->fetchAll(array(
            'folder_id = ?' => $id,
        ))->toArray();
    }

    /**
     * @see AbstractPlatform::doFindAllFiles
     */
    protected function doFindAllFiles()
    {
        return $this->getFileTable()->fetchAll(array(), "id ASC")->toArray();
    }

    /**
     * @see AbstractPlatform::doFindFile
     */
    protected function doFindFile($id)
    {
        return $this->getFileTable()->find($id)->current();
    }

    /**
     * @see AbstractPlatform::doUpdateFile
     */
    protected function doUpdateFile(File $file)
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
    protected function doDeleteFile(File $file)
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
    protected function doUpload(File $file, Folder $folder)
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

    /**
     * @see AbstractPlatform::doFindFileByFilename
     */
    protected function doFindFileByFilename(Folder $folder, $filename)
    {
        return $this->getFileTable()->fetchRow(array(
            'folder_id = ?' => $folder->getId(),
            'filename = ?'  => $filename,
        ));
    }

    /**
     * @see AbstractPlatform::fileToArray
     */
    protected function fileToArray($fileRow)
    {
        return array(
            'id'            => $fileRow['id'],
            'folder_id'     => $fileRow['folder_id'],
            'name'          => $fileRow['filename'],
            'profile'       => $fileRow['fileprofile'],
            'link'          => $fileRow['filelink'],
            'date_created' => new DateTime($fileRow['date_created']),
            'status'        => (int) $fileRow['status'],
            'uuid'          => $fileRow['uuid'],
            'resource'      => $this->resourceToArray($this->doFindResource($fileRow['resource_id'])),
            'versions'      => unserialize($fileRow['versions']),
        );
    }

    /**
     * @see AbstractPlatform::folderToArray
     */
    protected function folderToArray($folder)
    {
        return array(
            'id'        => (int) $folder['id'],
            'parent_id' => $folder['parent_id']
                               ? (int) $folder['parent_id']
                               : null,
            'name'      => $folder['foldername'],
            'url'       => $folder['folderurl'],
            'uuid'      => $folder['uuid'],
        );
    }

    /**
     * @see AbstractPlatform::doFindResource
     */
    protected function doFindResource($id)
    {
        return $this->getResourceTable()->find($id)->current();
    }

    /**
     * @see AbstractPlatform::doFindResourcesByHash
     */
    protected function doFindResourcesByHash($hash)
    {
        return $this->getResourceTable()->fetchAll(array(
            'hash = ?' => $hash,
        ))->toArray();
    }

    /**
     * @see AbstractPlatform::doCreateResource
     */
    protected function doCreateResource(Resource $resource)
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
    protected function doDeleteResource(Resource $resource)
    {
        $row = $this->getResourceTable()->find($resource->getId())->current();
        if (!$row) {
            return false;
        }
        $row->delete();
        return true;
    }

    /**
     * @see AbstractPlatform::resourceToArray
     */
    protected function resourceToArray($row)
    {
        return Resource::create(array(
            'id' => (int) $row['id'],
            'hash' => $row['hash'],
            'size' => (int) $row['filesize'],
            'mimetype' => $row['mimetype'],
            'date_created' => new DateTime($row['date_created']),
            'versions' => unserialize($row['versions']),
            'exclusive' => (bool) $row['exclusive'],
        ));
    }

    /**
     * @see AbstractPlatform::doGetNumberOfReferences
     */
    protected function doGetNumberOfReferences(Resource $resource)
    {
        return $this->db->fetchOne("SELECT COUNT(id) FROM xi_filelib_file WHERE resource_id = ?", array($resource->getId()));
    }

    /**
     * @see AbstractPlatform::isValidIdentifier
     */
    protected function isValidIdentifier($id)
    {
        return is_numeric($id);
    }
}
