<?php

namespace Xi\Filelib\Backend\ZendDb;

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
    protected $_rowClass = '\Xi\Filelib\Backend\ZendDb\FileRow';

    protected $_referenceMap    = array(
        'Folder' => array(
            'columns'           => 'folder_id',
            'refTableClass'     => '\Xi\Filelib\Backend\ZendDb\FolderTable',
            'refColumns'        => 'id'
               ),
            );


}
