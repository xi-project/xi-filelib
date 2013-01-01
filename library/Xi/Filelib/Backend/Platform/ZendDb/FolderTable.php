<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Platform\ZendDb;

/**
 * Folder table
 *
 * @author pekkis
 *
 */
class FolderTable extends \Zend_Db_Table_Abstract
{
    protected $_name = 'xi_filelib_folder';
    protected $_id = array('id');

    protected $_referenceMap    = array(
        'Folder' => array(
            'columns'           => 'parent_id',
            'refTableClass'     => '\Xi\Filelib\Backend\ZendDb\FolderTable',
            'refColumns'        => 'id'
        ),
    );
}
