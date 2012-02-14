<?php

namespace Xi\Filelib\Backend\ZendDb;

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
    protected $_rowClass = '\Xi\Filelib\Backend\ZendDb\FolderRow';


    protected $_referenceMap    = array(
        'Folder' => array(
            'columns'           => 'parent_id',
            'refTableClass'     => '\Xi\Filelib\Backend\ZendDb\FolderTable',
            'refColumns'        => 'id'
            ),
            );


}
