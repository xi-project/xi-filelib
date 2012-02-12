<?php

namespace Xi\Tests\Filelib\Backend\ZendDb;

class FolderRowTest extends \Xi\Tests\Filelib\TestCase
{
    
    public function setUp()
    {
        if (!class_exists("\Zend_Db_Table_Row_Abstract")) {
            $this->markTestSkipped('Zend DB not available');
        }
    }
    
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Backend\ZendDb\FolderRow'));
    }
    
    
}