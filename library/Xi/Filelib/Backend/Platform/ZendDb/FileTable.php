<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Platform\ZendDb;

/**
 * File table
 *
 * @author pekkis
 *
 */
class FileTable extends \Zend_Db_Table_Abstract
{
    protected $_name = 'xi_filelib_file';
    protected $_id = array('id');

    protected $_referenceMap    = array(
        'Folder' => array(
            'columns'           => 'folder_id',
            'refTableClass'     => 'Xi\Filelib\Backend\ZendDb\FolderTable',
            'refColumns'        => 'id'
               ),
        'Resource' => array(
            'columns'           => 'resource_id',
            'refTableClass'     => 'Xi\Filelib\Backend\ZendDb\ResourceTable',
            'refColumns'        => 'id'
        ),
    );
}
