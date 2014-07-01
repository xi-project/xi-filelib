<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Backend\Finder\Finder;
use Doctrine\DBAL\Connection;
use PDO;
use ArrayIterator;
use Iterator;
use DateTime;

/**
 * Doctrine Dbal backend for filelib. Only supports postgresql and mysql because of portability stuff.
 * Strongly suggest you use the ORM version because it is much more portable.
 */
class DoctrineDbalBackendAdapter implements BackendAdapter
{
    private $supportedPlatforms = array('postgresql', 'mysql');

    /**
     * @var array
     */
    private $finderMap = array(
        'Xi\Filelib\Resource\Resource' => array(
            'id' => 'id',
            'hash' => 'hash',
        ),
        'Xi\Filelib\File\File' => array(
            'id' => 'id',
            'folder_id' => 'folder_id',
            'name' => 'filename',
            'uuid' => 'uuid',
        ),
        'Xi\Filelib\Folder\Folder' => array(
            'id' => 'id',
            'parent_id' => 'parent_id',
            'url' => 'folderurl',
        ),
    );

    private $classNameToResources = array(
        'Xi\Filelib\Resource\Resource' => array(
            'table' => 'xi_filelib_resource',
            'exporter' => 'exportResources',
            'getEntityName' => 'getResourceEntityName',
        ),
        'Xi\Filelib\File\File' => array(
            'table' => 'xi_filelib_file',
            'exporter' => 'exportFiles',
            'getEntityName' => 'getFileEntityName',
        ),
        'Xi\Filelib\Folder\Folder' => array(
            'table' => 'xi_filelib_folder',
            'exporter' => 'exportFolders',
            'getEntityName' => 'getFolderEntityName',
        ),
    );

    /**
     * @param Connection $conn
     */
    public function __construct(Connection $conn)
    {
        $this->conn = $conn;

        if (!$this->isPlatformSupported($this->conn->getDatabasePlatform())) {
            throw new \RuntimeException("Unsupported Doctrine platform");
        }
    }

    public function isOrigin()
    {
        return true;
    }

    /**
     * @see BackendAdapter::updateFile
     */
    public function updateFile(File $file)
    {
        $sql = "
        UPDATE xi_filelib_file
        SET folder_id = :folderId, fileprofile = :profile, filename = :name, date_created = :dateCreated,
        status = :status,uuid = :uuid, resource_id = :resourceId, data = :data
        WHERE id = :id
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute(
            array(
                'folderId' => $file->getFolderId(),
                'profile' => $file->getProfile(),
                'name' => $file->getName(),
                'dateCreated' => $file->getDateCreated()->format('Y-m-d H:i:s'),
                'status' => $file->getStatus(),
                'uuid' => $file->getUuid(),
                'resourceId' => $file->getResource()->getId(),
                'data' => json_encode($file->getdata()->toArray()),
                'id' => $file->getId(),
            )
        );

        return (bool) $stmt->rowCount();
    }

    /**
     * @see BackendAdapter::deleteFile
     */
    public function deleteFile(File $file)
    {
        $stmt = $this->conn->prepare($sql = "DELETE FROM xi_filelib_file WHERE id = ?");
        $stmt->execute(array($file->getId()));

        return (bool) $stmt->rowCount();
    }

    /**
     * @see BackendAdapter::createFolder
     */
    public function createFolder(Folder $folder)
    {
        $id = ($this->conn->getDatabasePlatform()->getName() == 'mysql') ?
            null : $this->conn->fetchColumn(
                $this->conn->getDatabasePlatform()->getSequenceNextValSQL('xi_filelib_folder_id_seq')
            );

        $this->conn->insert(
            'xi_filelib_folder',
            array(
                'id' => $id,
                'parent_id' => $folder->getParentId(),
                'foldername' => $folder->getName(),
                'folderurl' => $folder->getUrl(),
                'uuid' => $folder->getUuid(),
                'data' => json_encode($folder->getdata()->toArray())
            )
        );

        $id = $this->conn->getDatabasePlatform()->getName() == 'mysql' ? $this->conn->lastInsertId() : $id;
        $folder->setId($id);
        return $folder;
    }

    /**
     * @see BackendAdapter::updateFolder
     */
    public function updateFolder(Folder $folder)
    {
        $sql = "
        UPDATE xi_filelib_folder
        SET parent_id = :parentId, foldername = :name, folderurl = :url, uuid = :uuid, data = :data
        WHERE id = :id
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->execute(
            array(
                'parentId' => $folder->getParentId(),
                'name' => $folder->getName(),
                'url' => $folder->getUrl(),
                'uuid' => $folder->getUuid(),
                'id' => $folder->getId(),
                'data' => json_encode($folder->getdata()->toArray())
            )
        );

        return (bool) $stmt->rowCount();
    }

    /**
     * @see BackendAdapter::updateResource
     */
    public function updateResource(Resource $resource)
    {
        $sql = "
        UPDATE xi_filelib_resource
        SET hash = :hash, exclusive = :exclusive, data = :data
        WHERE id = :id
        ";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindValue('hash', $resource->getHash(), PDO::PARAM_STR);
        $stmt->bindValue('exclusive', $resource->isExclusive(), PDO::PARAM_BOOL);
        $stmt->bindValue('data', json_encode($resource->getData()->toArray()), PDO::PARAM_STR);
        $stmt->bindValue('id', $resource->getId(), PDO::PARAM_INT);

        $stmt->execute();
        return (bool) $stmt->rowCount();
    }

    /**
     * @see BackendAdapter::deleteFolder
     */
    public function deleteFolder(Folder $folder)
    {
        $stmt = $this->conn->prepare($sql = "DELETE FROM xi_filelib_folder WHERE id = ?");
        $stmt->execute(array($folder->getId()));

        return (bool) $stmt->rowCount();
    }

    /**
     * @see BackendAdapter::deleteResource
     */
    public function deleteResource(Resource $resource)
    {
        $stmt = $this->conn->prepare($sql = "DELETE FROM xi_filelib_resource WHERE id = ?");
        $stmt->execute(array($resource->getId()));

        return (bool) $stmt->rowCount();
    }

    /**
     * @see BackendAdapter::createResource
     */
    public function createResource(Resource $resource)
    {
        $id = ($this->conn->getDatabasePlatform()->getName() == 'mysql') ?
            null : $this->conn->fetchColumn(
                $this->conn->getDatabasePlatform()->getSequenceNextValSQL('xi_filelib_resource_id_seq')
            );

        $this->conn->insert(
            'xi_filelib_resource',
            array(
                'id' => $id,
                'hash' => $resource->getHash(),
                'date_created' => $resource->getDateCreated()->format('Y-m-d H:i:s'),
                'mimetype' => $resource->getMimetype(),
                'exclusive' => $resource->isExclusive(),
                'filesize' => $resource->getSize(),
                'data' => json_encode($resource->getData()->toArray()),
            ),
            array(
                PDO::PARAM_INT,
                PDO::PARAM_STR,
                PDO::PARAM_STR,
                PDO::PARAM_STR,
                PDO::PARAM_BOOL,
                PDO::PARAM_INT,
                PDO::PARAM_STR,
            )
        );

        $id = $this->conn->getDatabasePlatform()->getName() == 'mysql' ? $this->conn->lastInsertId() : $id;
        $resource->setId($id);

        return $resource;
    }

    /**
     * @see BackendAdapter::createFile
     */
    public function createFile(File $file, Folder $folder)
    {
        $id = ($this->conn->getDatabasePlatform()->getName() == 'mysql') ?
            null : $this->conn->fetchColumn(
                $this->conn->getDatabasePlatform()->getSequenceNextValSQL('xi_filelib_file_id_seq')
            );

        $this->conn->insert(
            'xi_filelib_file',
            array(
                'id' => $id,
                'folder_id' => $folder->getId(),
                'fileprofile' => $file->getProfile(),
                'filename' => $file->getName(),
                'date_created' => $file->getDateCreated()->format('Y-m-d H:i:s'),
                'status' => $file->getStatus(),
                'uuid' => $file->getUuid(),
                'resource_id' => $file->getResource()->getId(),
                'data' => json_encode($file->getdata()->toArray()),
            )
        );

        $id = $this->conn->getDatabasePlatform()->getName() == 'mysql' ? $this->conn->lastInsertId() : $id;
        $file->setId($id);
        $file->setFolderId($folder->getId());
        return $file;
    }

    /**
     * @see BackendAdapter::getNumberOfReferences
     */
    public function getNumberOfReferences(Resource $resource)
    {
        return $this->conn->fetchColumn(
            "SELECT COUNT(id) FROM xi_filelib_file WHERE resource_id = ?",
            array(
                $resource->getId()
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

        $tableName = $resources['table'];
        $conn = $this->conn;

        $qb = $conn->createQueryBuilder();
        $qb->select("id")->from($tableName, 't');

        $bindParams = array();
        foreach ($params as $param => $value) {

            if ($value === null) {
                $qb->andWhere("t.{$param} IS NULL");
            } else {
                $qb->andWhere("t.{$param} = :{$param}");
                $bindParams[$param] = $value;
            }

        }

        $sql = $qb->getSQL();
        $stmt = $conn->prepare($sql);
        foreach ($bindParams as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();

        $ret = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(
            function ($ret) {
                return $ret['id'];
            },
            $ret
        );
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
        $tableName = $resources['table'];

        $ids = implode(', ', $ids);
        $rows = $this->conn->fetchAll(
            "SELECT * FROM {$tableName} WHERE id IN ({$ids})"
        );
        $rows = new ArrayIterator($rows);

        return $request->foundMany($this->$resources['exporter']($rows));
    }

    /**
     * @param  Iterator      $iter
     * @return ArrayIterator
     */
    protected function exportFolders(Iterator $iter)
    {
        $ret = new ArrayIterator(array());
        foreach ($iter as $folder) {
            $ret->append(
                Folder::create(
                    array(
                        'id' => $folder['id'],
                        'parent_id' => $folder['parent_id'],
                        'name' => $folder['foldername'],
                        'url' => $folder['folderurl'],
                        'uuid' => $folder['uuid'],
                        'data' => json_decode($folder['data'], true),
                    )
                )
            );
        }

        return $ret;
    }

    /**
     * @param  Iterator      $iter
     * @return ArrayIterator
     */
    protected function exportFiles(Iterator $iter)
    {
        $ret = new ArrayIterator(array());
        foreach ($iter as $file) {

            $request = new FindByIdsRequest(array($file['resource_id']), 'Xi\Filelib\Resource\Resource');
            $resource = $this->findByIds($request)->getResult()->first();

            $ret->append(
                File::create(
                    array(
                        'id' => $file['id'],
                        'folder_id' => $file['folder_id'],
                        'profile' => $file['fileprofile'],
                        'name' => $file['filename'],
                        'date_created' => new DateTime($file['date_created']),
                        'status' => $file['status'],
                        'uuid' => $file['uuid'],
                        'resource' => $resource,
                        'data' => json_decode($file['data'], true),
                    )
                )
            );
        }

        return $ret;
    }

    /**
     * @param  Iterator      $iter
     * @return ArrayIterator
     */
    protected function exportResources(Iterator $iter)
    {
        $ret = new ArrayIterator(array());
        foreach ($iter as $resource) {
            $ret->append(
                Resource::create(
                    array(
                        'id' => $resource['id'],
                        'hash' => $resource['hash'],
                        'date_created' => new DateTime($resource['date_created']),
                        'data' => json_decode($resource['data'], true),
                        'mimetype' => $resource['mimetype'],
                        'size' => $resource['filesize'],
                        'exclusive' => (bool) $resource['exclusive'],
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


    /**
     * @param AbstractPlatform $platform
     * @return bool
     */
    private function isPlatformSupported(AbstractPlatform $platform)
    {
        return in_array($platform->getName(), $this->supportedPlatforms);
    }
}
