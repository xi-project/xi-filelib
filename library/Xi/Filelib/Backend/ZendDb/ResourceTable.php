<?php

namespace Xi\Filelib\Backend\ZendDb;

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
