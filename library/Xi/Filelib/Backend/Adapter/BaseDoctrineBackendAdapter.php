<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Adapter;

use Doctrine\DBAL\Connection;
use PDO;
use Xi\Filelib\Backend\Finder\Finder;

/**
 * Doctrine Dbal backend for filelib. Only supports postgresql and mysql because of portability stuff.
 * Strongly suggest you use the ORM version because it is much more portable.
 */
abstract class BaseDoctrineBackendAdapter
{
    /**
     * @var array
     */
    protected $finderMap = array(
        'Xi\Filelib\Resource\ConcreteResource' => array(
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

    protected $classNameToResources = array(
        'Xi\Filelib\Resource\ConcreteResource' => array(
            'table' => 'xi_filelib_resource',
        ),
        'Xi\Filelib\File\File' => array(
            'table' => 'xi_filelib_file',
            'exporter' => 'exportFiles',
        ),
        'Xi\Filelib\Folder\Folder' => array(
            'table' => 'xi_filelib_folder',
            'exporter' => 'exportFolders',
        ),
    );

    public function isOrigin()
    {
        return true;
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
     * @see BackendAdapter::findByFinder
     */
    public function findByFinder(Finder $finder)
    {
        $resources = $this->classNameToResources[$finder->getResultClass()];
        $params = $this->finderParametersToInternalParameters($finder);

        $tableName = $resources['table'];
        $conn = $this->getConnection();

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
     * @return Connection
     */
    abstract protected function getConnection();
}
