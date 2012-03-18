<?php

namespace Xi\Tests\Filelib\Tool\Slugifier;

use \Xi\Filelib\Tool\Slugifier;

class TestCase extends \Xi\Tests\Filelib\TestCase {
    
    /**
     *
     * @var Slugifier
     */
    protected $slugifier;
    
    
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
            array('aaaaaaccccccccddeeeeeeeeeegggg', 'ĀāĂăĄąĆćĈĉĊċČčĎďĒēĔĕĖėĘęĚěĜĝĞğ'),
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
