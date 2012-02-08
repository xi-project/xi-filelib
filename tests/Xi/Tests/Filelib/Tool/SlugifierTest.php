<?php

namespace Xi\Tests\Filelib\Tool;

use \Xi\Filelib\Tool\Slugifier;

class SlugifierTest extends \Xi\Tests\Filelib\TestCase {
    
    /**
     *
     * @var Slugifier
     */
    protected $slugifier;
    
    public function setUp()
    {
        if (!class_exists('\\Zend\\Filter\\FilterChain')) {
            $this->markTestSkipped('Zend Framework 2 filters not loadable');
        }

        
        $this->slugifier = new Slugifier();
    }
    
    
    
    /**
     * @test
     */
    public function slugifierShouldSlugifySimpleNonUnicodeStringsProperly()
    {
        
        $this->assertEquals('peksu-con', $this->slugifier->slugify('peksu con'));
        $this->assertEquals('lussuti-lussuti', $this->slugifier->slugify('lussuti_lussuti'));
        $this->assertEquals('suuren-ugrilaisen-kansan-sielu', $this->slugifier->slugify('SUUrEN ugRILAIseN kanSAn SIELU'));
        
    }
    
    
    public function provideWeirdUnicodeStrings()
    {
        return array(
            array('uber', 'über'),
            array('aaaaaaccccccccddddeeeeeeeeeegggg', 'ĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğ'),
            array('oyao', 'ôÿäö'),
            array('suuren-ugrilaisen-kansan-sielu', 'sûürën ÜGRÎLÄISÊN KÄNSÄN SïëLú'),
        );
    }
    
    
    
    
    /**
     * @test
     * @dataProvider provideWeirdUnicodeStrings
     */
    public function slugifierShouldSlugifyUnicodeStringsProperly($slugged, $unslugged)
    {
        $this->assertEquals($slugged, $this->slugifier->slugify($unslugged));
        
        $this->assertEquals('uber', $this->slugifier->slugify('über'));
        // $this->assertEquals('aaaaaaccccccccddddeeeeeeeeeegggg', $this->slugifier->slugify('ĀāĂăĄąĆćĈĉĊċČčĎďĐđĒēĔĕĖėĘęĚěĜĝĞğ'));
        
    }
    
    
    /**
     * @test
     */
    public function slugifyPathShouldSlugifyAllPartsOfAPath()
    {
        $this->assertEquals('suuren/ugrilaisen/kansan/sielu', $this->slugifier->slugifyPath('sûürën/ÜGRÎLÄISÊN/KÄNSÄN/SïëLú'));
    }
    
    
    
}
