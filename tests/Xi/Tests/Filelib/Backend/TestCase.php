<?php

namespace Xi\Tests\Filelib\Backend;

use \PDO;

abstract class TestCase extends \PHPUnit_Extensions_Database_TestCase {
    

    protected $connection;
    
    
    /**
     * @return \PHPUnit_Extensions_Database_DB_IDatabaseConnection
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $dsn = 'sqlite:' . ROOT_TESTS . '/data/filelib-test.db';
        
            $pdo = new PDO($dsn);
        
            $this->connection = $this->createDefaultDBConnection($pdo);
            
        }
        
        
        return $this->connection;
        
        
    }
    
    
    
}
