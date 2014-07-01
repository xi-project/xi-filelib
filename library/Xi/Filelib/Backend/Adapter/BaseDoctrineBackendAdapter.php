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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Iterator;
use PDO;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Backend\Finder\Finder;
use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Resource\Resource;

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

    protected $classNameToResources = array(
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
     * @param AbstractPlatform $platform
     * @return bool
     */
    private function isPlatformSupported(AbstractPlatform $platform)
    {
        return in_array($platform->getName(), $this->supportedPlatforms);
    }
}
