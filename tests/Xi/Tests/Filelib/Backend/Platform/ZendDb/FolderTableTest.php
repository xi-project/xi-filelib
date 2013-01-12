<?php

namespace Xi\Tests\Filelib\Backend\Platform\ZendDb;

class FolderTableTest extends \Xi\Tests\Filelib\TestCase
{
    public function setUp()
    {
        if (!class_exists("\Zend_Db_Table_Abstract")) {
            $this->markTestSkipped('Zend DB not available');
        }
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Backend\Platform\ZendDb\FolderTable'));
    }
}
