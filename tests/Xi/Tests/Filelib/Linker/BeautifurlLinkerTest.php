<?php

use \Xi\Filelib\Folder\FolderItem;

use \Xi\Filelib\File\FileItem;

use \Xi\Filelib\Linker\BeautifurlLinker;

class BeautifurlLinkerTest extends \Xi\Tests\Filelib\TestCase
{

    protected $filelib;
    
    
    
    
    
    public function setUp()
    {
        if (!class_exists('\\Zend\\Filter\\FilterChain')) {
            $this->markTestSkipped('Zend Framework 2 filters not loadable');
        }
                
        $fo = $this->getMockBuilder('\Xi\Filelib\Folder\FolderOperator')->getMock();
        $fo->expects($this->any())
             ->method('find')
             ->will($this->returnCallback(function($id) {
                 
                 if ($id == 1) {
                     
                     return FolderItem::create(array(
                         'id' => 1,
                         'name' => 'root',
                         'parent_id' => null,
                         'url' => ''
                     ));
                     
                     
                     
                 } elseif($id == 2) {
                     
                     return FolderItem::create(array(
                         'id' => 2,
                         'name' => 'lussuttaja',
                         'parent_id' => 1,
                         'url' => '/lussuttaja'
                     ));
                     
                 } elseif($id == 3) {
                     
                     return FolderItem::create(array(
                         'id' => 2,
                         'name' => 'tussin',
                         'parent_id' => 2,
                         'url' => '/lussuttaja/tussin'
                     ));
                     
                     
                 } elseif($id == 4) {
                     
                     return FolderItem::create(array(
                         'id' => 2,
                         'name' => 'banaanin',
                         'parent_id' => 2,
                         'url' => '/lussuttaja/banaanin'
                     ));
                     
                 } elseif($id == 5) {

                     return FolderItem::create(array(
                         'id' => 5,
                         'name' => 'sûürën ÜGRÎLÄISÊN KÄNSÄN SïëLú',
                         'parent_id' => 4,
                         'url' => '/lussuttaja/banaanin/suuren-ugrilaisen-kansan-sielu'
                     ));
                 }
                 
                 return null;
                 
             }));

        
        
        $this->fo = $fo;
        

        $this->filelib = $this->getFilelib();
        
        $this->filelib->setFolderOperator($fo);
        
        $vp = $this->getMock('\Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $vp->expects($this->any())
             ->method('getIdentifier')
             ->will($this->returnValue('xoo'));

        $vp->expects($this->any())
             ->method('getExtension')
             ->will($this->returnValue('xoo'));

        
        
        $this->versionProvider = $vp;

        
        
    }
    
    
    
    public function provideFiles()
    {
        return array(
            array(
                FileItem::create(array(
                    'name' => 'loso.png',
                    'folder_id' => 3
                    
                )), array('lussuttaja/tussin/loso.png', 'lussuttaja/tussin/loso-xoo.xoo'),
                
            ),         
            array(
                FileItem::create(array(
                    'name' => 'kim-jong-il',
                    'folder_id' => 4
                    
                )), array('lussuttaja/banaanin/kim-jong-il', 'lussuttaja/banaanin/kim-jong-il-xoo.xoo'),
                
            ),         
            array(
                FileItem::create(array(
                    'name' => 'juurekas.nom',
                    'folder_id' => 1
                    
                )), array('juurekas.nom', 'juurekas-xoo.xoo'),
                
            ),         
            array(
                FileItem::create(array(
                    'name' => 'salainen-suunnitelma.pdf',
                    'folder_id' => 5
                    
                )), array('lussuttaja/banaanin/suuren-ugrilaisen-kansan-sielu/salainen-suunnitelma.pdf', 'lussuttaja/banaanin/suuren-ugrilaisen-kansan-sielu/salainen-suunnitelma-xoo.xoo'),
                
            ),         

        );
    }
    
    
    
    /**
     * @test
     * @dataProvider provideFiles
     */
    public function linkerShouldCreateProperBeautifurlLinks($file, $beautifurl)
    {
        $linker = new BeautifurlLinker();
        $linker->setFilelib($this->filelib);
        $linker->setExcludeRoot(true);
                
        $this->assertEquals($beautifurl[0], $linker->getLink($file, true));
        
    }
    
    /**
     *
     * 
     * @test
     * @dataProvider provideFiles
     */
    public function versionLinkerShouldCreateProperBeautifurlLinks($file, $beautifurl)
    {
        $linker = new BeautifurlLinker();
        $linker->setFilelib($this->filelib);
        $linker->setExcludeRoot(true);
                
        $this->assertEquals($beautifurl[1], $linker->getLinkVersion($file, $this->versionProvider, true));
        
    }
    
    
    
    
    
    /**
     * @test
     */
    public function linkerShouldExcludeRootProperly()
    {
        
        $linker = new BeautifurlLinker();
        $linker->setFilelib($this->filelib);
        
        
        $file = FileItem::create(array(
            'name' => 'lamantiini.lus',
            'folder_id' => 2,
        ));

        $linker->setExcludeRoot(false);                
        $this->assertEquals('root/lussuttaja/lamantiini.lus', $linker->getLink($file, true));
                
        $linker->setExcludeRoot(true);                
        $this->assertEquals('lussuttaja/lamantiini.lus', $linker->getLink($file, true));
                
    }
    
    
    
}
