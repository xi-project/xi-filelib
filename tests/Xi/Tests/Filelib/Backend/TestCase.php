<?php

namespace Xi\Tests\Filelib\Backend;

use \PDO;

abstract class TestCase extends \PHPUnit_Extensions_Database_TestCase {
    

    protected $connection;
    
    
    /**
     * @return PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getDataSet()
    {
        return new ArrayDataSet(array(
            'xi_filelib_folder' => array(

                array(
                    'id' => 1,
                    'parent_id' => null,
                    'folderurl' => '',
                    'foldername' => 'root',
                ),
                
                array(
                    'id' => 2,
                    'parent_id' => 1,
                    'folderurl' => 'lussuttaja',
                    'foldername' => 'lussuttaja',
                ),
                
                array(
                    'id' => 3,
                    'parent_id' => 2,
                    'folderurl' => 'lussuttaja/tussin',
                    'foldername' => 'tussin',
                ),

                array(
                    'id' => 4,
                    'parent_id' => 2,
                    'folderurl' => 'lussuttaja/banskun',
                    'foldername' => 'banskun',
                ),
                
                
            ),
        ));
    }

    
    
    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        $dsn = 'sqlite:' . ROOT_TESTS . '/data/filelib-test.db';

        $pdo = new PDO($dsn);

        return $this->createDefaultDBConnection($pdo);
        
    }
    
    
    
}
