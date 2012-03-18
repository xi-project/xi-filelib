<?php

namespace Xi\Tests\Filelib\Tool\Slugifier;

use Xi\Filelib\Tool\Slugifier\ZendSlugifier;

class ZendSlugifierTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists('\Zend_Filter')) {
            $this->markTestSkipped('Zend Framework 1 filters not loadable');
        }
        
        $this->slugifier = new ZendSlugifier();
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Tool\Slugifier\ZendSlugifier'));
        $this->assertContains('Xi\Filelib\Tool\Slugifier\AbstractZendSlugifier', class_parents('Xi\Filelib\Tool\Slugifier\ZendSlugifier'));
        $this->assertContains('Xi\Filelib\Tool\Slugifier\Slugifier', class_implements('Xi\Filelib\Tool\Slugifier\ZendSlugifier'));
    }
    
    /**
     * @test
     */
    public function getFilterShouldReturnAnInstanceOfZendFilterAndCacheItsResult()
    {
        
        $slugifier = new ZendSlugifier();
        $filter = $slugifier->getFilter();
        
        $this->assertInstanceOf('Zend_Filter', $filter);
        
        $filter2 = $slugifier->getFilter();
        
        $this->assertSame($filter, $filter2);
        
    }
    
    

    
    
}
