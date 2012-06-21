<?php

namespace Xi\Tests\Filelib\Tool\Slugifier;

use Xi\Filelib\Tool\Slugifier\ZendSlugifier;

class ZendSlugifierTest extends TestCase
{
    public function setUp()
    {
        if (!class_exists("Transliterator")) {
            $this->markTestSkipped("Transliterator class (from intl extension 2.0+) not found");
        }

        if (!class_exists('\Zend_Filter')) {
            $this->markTestSkipped('Zend Framework 1 filters not loadable');
        }

        $trans = $this->getMock('Xi\Filelib\Tool\Transliterator\Transliterator');
        $trans->expects($this->any())->method('transliterate')->will($this->returnArgument(0));
        $this->slugifier = new ZendSlugifier($trans);
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
        // Fucking kludgings to prevent autoloading collision with ZF1 and ZF2.
        require_once "Zend/Filter/Word/UnderscoreToSeparator.php";
        require_once "Zend/Filter/Alnum.php";
        require_once "Zend/Filter/StringToLower.php";
        require_once "Zend/Filter/Word/SeparatorToDash.php";
        
        $slugifier = new ZendSlugifier($this->getMock('Xi\Filelib\Tool\Transliterator\Transliterator'));
        $filter = $slugifier->getFilter();
        
        $this->assertInstanceOf('Zend_Filter', $filter);
        
        $filter2 = $slugifier->getFilter();
        
        $this->assertSame($filter, $filter2);
        
    }
    
    

    
    
}
