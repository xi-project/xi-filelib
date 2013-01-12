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
class ResourceTable extends \Zend_Db_Table_Abstract
{
    protected $_name = 'xi_filelib_resource';
    protected $_id = array('id');
}
