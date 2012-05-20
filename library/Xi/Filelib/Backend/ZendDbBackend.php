<?php

namespace Xi\Filelib\Backend;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Exception\NonUniqueFileException;
use Xi\Filelib\Backend\ZendDb\FolderRow;
use Xi\Filelib\Backend\ZendDb\FileTable;
use Xi\Filelib\Backend\ZendDb\FolderTable;
use Zend_Db_Adapter_Abstract;
use Zend_Db_Table_Abstract;
use Zend_Db_Statement_Exception;
use DateTime;

/**
 * ZendDb backend for filelib
 *
 * @author   pekkis
 * @category Xi
 * @package  Filelib
 */
class ZendDbBackend extends AbstractBackend implements Backend
{
    /**
     * @var Zend_Db_Adapter_Abstract Zend Db adapter
     */
    private $db;

    /**
     * @var FileTable File table
     */
    private $fileTable;

    /**
     * @var FolderTable Folder table
     */
    private $folderTable;

    /**
     * @param  Zend_Db_Adapter_Abstract $db
     * @return ZendDbBackend
     */
    public function __construct(Zend_Db_Adapter_Abstract $db)
    {
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
     * @return ZendDbBackend
     */
    public function setFolderTable(Zend_Db_Table_Abstract $folderTable)
    {
        $this->folderTable = $folderTable;

        return $this;
    }

    /**
     * @return object
     */
    protected function doFindRootFolder()
    {
        $folder = $this->getFolderTable()->fetchRow(array('parent_id IS NULL'));

        if (!$folder) {
            $folder = $this->getFolderTable()->createRow();

            $folder->foldername = 'root';
            $folder->parent_id  = null;
            $folder->folderurl  = '';

            $folder->save();
        }

        return $folder;
    }

    /**
     * @param  integer   $id
     * @return FolderRow
     */
    protected function doFindFolder($id)
    {
        return $this->getFolderTable()->find($id)->current();
    }

    /**
     * @param  Folder $folder
     * @return Folder
     */
    protected function doCreateFolder(Folder $folder)
    {
        $folderRow = $this->getFolderTable()->createRow();
        $folderRow->foldername = $folder->getName();
        $folderRow->parent_id  = $folder->getParentId();
        $folderRow->folderurl  = $folder->getUrl();

        $folderRow->save();

        $folder->setId((int) $folderRow->id);

        return $folder;
    }

    /**
     * @param  Folder $folder
     * @return boolean
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
     * @param  Folder  $folder
     * @return boolean
     */
    protected function doUpdateFolder(Folder $folder)
    {
        return (bool) $this->getFolderTable()->update(
            array(
                'id'         => $folder->getId(),
                'parent_id'  => $folder->getParentId(),
                'foldername' => $folder->getName(),
                'folderurl'  => $folder->getUrl(),
            ),
            $this->getFolderTable()
                 ->getAdapter()
                 ->quoteInto('id = ?', $folder->getId())
        );
    }

    /**
     * @param  integer $id
     * @return array
     */
    protected function doFindSubFolders($id)
    {
        return $this->getFolderTable()->fetchAll(array(
            'parent_id = ?' => $id,
        ))->toArray();
    }

    /**
     * @param  string     $url
     * @return array|null
     */
    protected function doFindFolderByUrl($url)
    {
        return $this->getFolderTable()->fetchRow(array(
            'folderurl = ?' => $url,
        ));
    }

    /**
     * @param  integer $id
     * @return array
     */
    protected function doFindFilesIn($id)
    {
        return $this->getFileTable()->fetchAll(array(
            'folder_id = ?' => $id,
        ))->toArray();
    }

    /**
     * @return array
     */
    protected function doFindAllFiles()
    {
        return $this->getFileTable()->fetchAll(array(), "id ASC")->toArray();
    }

    /**
     * @param  integer    $id
     * @return array|null
     */
    protected function doFindFile($id)
    {
        return $this->getFileTable()->find($id)->current();
    }

    /**
     * @param  File    $file
     * @return boolean
     */
    protected function doUpdateFile(File $file)
    {
        $data = $file->toArray();

        return (bool) $this->getFileTable()->update(
            array(
                'folder_id'     => $data['folder_id'],
                'mimetype'      => $data['mimetype'],
                'filesize'      => $data['size'],
                'filename'      => $data['name'],
                'fileprofile'   => $data['profile'],
                'date_uploaded' => $data['date_uploaded']->format('Y-m-d H:i:s'),
                'filelink'      => $data['link'],
                'status'        => $data['status'],
            ),
            $this->getFileTable()
                 ->getAdapter()
                 ->quoteInto('id = ?', $data['id'])
        );
    }

    /**
     * @param  File    $file
     * @return boolean
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
     * @param  File                   $file
     * @param  Folder                 $folder
     * @return File
     * @throws NonUniqueFileException If file already exists folder
     */
    protected function doUpload(File $file, Folder $folder)
    {
        $row = $this->getFileTable()->createRow();

        $row->folder_id     = $folder->getId();
        $row->mimetype      = $file->getMimeType();
        $row->filesize      = $file->getSize();
        $row->filename      = $file->getName();
        $row->fileprofile   = $file->getProfile();
        $row->date_uploaded = $file->getDateUploaded()->format('Y-m-d H:i:s');
        $row->status        = $file->getStatus();

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
     * @param  Folder     $folder
     * @param  string     $filename
     * @return array|null
     */
    protected function doFindFileByFilename(Folder $folder, $filename)
    {
        return $this->getFileTable()->fetchRow(array(
            'folder_id = ?' => $folder->getId(),
            'filename = ?'  => $filename,
        ));
    }

    /**
     * @param  FileRow $row
     * @return array
     */
    protected function fileToArray($fileRow)
    {
        return array(
            'id'            => $fileRow['id'],
            'folder_id'     => $fileRow['folder_id'],
            'mimetype'      => $fileRow['mimetype'],
            'size'          => $fileRow['filesize'],
            'name'          => $fileRow['filename'],
            'profile'       => $fileRow['fileprofile'],
            'link'          => $fileRow['filelink'],
            'date_uploaded' => new DateTime($fileRow['date_uploaded']),
            'status'        => $fileRow['status'],
        );
    }

    /**
     * @param  FolderRow $folder
     * @return array
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
        );
    }

    protected function doFindResource($id)
    {
        throw new \LogicException('I am not implementeed');
    }

    protected function doFindResourcesByHash($hash)
    {
        throw new \LogicException('I am not implementeed');
    }

    protected function doCreateResource(Resource $resource)
    {
        throw new \LogicException('I am not implementeed');
    }

    protected function doDeleteResource(Resource $resource)
    {
        throw new \LogicException('I am not implementeed');
    }

    /**
     * @param mixed $resource
     * @return array
     */
    protected function resourceToArray($resource)
    {
        throw new \LogicException('I am not implementeed');
    }

}
