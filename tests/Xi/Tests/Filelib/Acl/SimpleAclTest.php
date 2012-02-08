<?php

namespace Xi\Tests\Filelib\Acl;

use \PHPUnit_Framework_TestCase as TestCase;

use Xi\Filelib\Folder\FolderItem;
use Xi\Filelib\File\FileItem;

use Xi\Filelib\Acl\Acl,
    Xi\Filelib\Acl\SimpleAcl;

class SimpleAclTest extends TestCase
{
    
    /**
     *
     * @var Acl
     */
    private $acl;
    
    
    public function setUp()
    {
        $this->acl = new SimpleAcl(true);
    }
    
    
    public function provideFolders()
    {
        return array(
            array(
                array(
                    'id' => 1,
                    'parent_id' => null,
                    'url' => 'lussuttaja',
                    'name' => 'lussander',
                ),
                true,
                true,
                true,
            ),
            array(
                array(
                    'id' => 2,
                    'parent_id' => 1,
                    'url' => 'lussuttaja/tussi',
                    'name' => 'tussi',
                ),
                true,
                true,
                true
            ),
        );
        
    }
    
    
    
    
    public function provideFiles()
    {
    
        return array(
            array(
                array(
                   'id' => 1,
                    'folder_id' => 1,
                    'url' => 'tohtori-vesala.png',
                    'name' => 'tohtori-vesala.png',
                ),
                true,
                true,
                true
            ),
            
            array(
                array(
                   'id' => 2,
                    'folder_id' => 2,
                    'url' => 'tohtori-sykero.png',
                    'name' => 'tohtori-sykero.png',
                ),
                true,
                true,
                true
            ),

            array(
                array(
                   'id' => 3,
                    'folder_id' => 3,
                    'url' => 'tohtori-tussi.png',
                    'name' => 'tohtori-tussi.png',
                ),
                true,
                true,
                true
            ),
            
        );
        
    }
    
    
    /**
     * @test
     * @dataProvider provideFiles
     */
    public function isReadableShouldReturnExpectedResultsForFiles($res, $readable, $writeable, $readableByAnonymous)
    {
        $res = FileItem::create($res);        
        $this->assertEquals($readable, $this->acl->isReadable($res));
    }
    
    
    /**
     * @test
     * @dataProvider provideFolders
     */
    public function IsReadableShouldReturnExpectedResultForFolders($res, $readable, $writeable, $readableByAnonymous)
    {
        $res = FolderItem::create($res);        
        $this->assertEquals($readable, $this->acl->isReadable($res));
    }

    
    /**
     * @test
     * @dataProvider provideFiles
     */
    public function isWriteableShouldReturnExpectedResultsForFiles($res, $readable, $writeable, $readableByAnonymous)
    {
        $res = FileItem::create($res);        
        $this->assertEquals($writeable, $this->acl->isWriteable($res));
    }
    
    
    /**
     * @test
     * @dataProvider provideFolders
     */
    public function IsWriteableShouldReturnExpectedResultForFolders($res, $readable, $writeable, $readableByAnonymous)
    {
        $res = FolderItem::create($res);        
        $this->assertEquals($writeable, $this->acl->isWriteable($res));
    }
    
    
    /**
     * @test
     * @dataProvider provideFiles
     */
    public function isAnonymousReadableShouldReturnExpectedResultsForFiles($res, $readable, $writeable, $readableByAnonymous)
    {
        $res = FileItem::create($res);        
        $this->assertEquals($readableByAnonymous, $this->acl->isReadableByAnonymous($res));
    }
    
    
    /**
     * @test
     * @dataProvider provideFolders
     */
    public function IsAnonymousReadableShouldReturnExpectedResultForFolders($res, $readable, $writeable, $readableByAnonymous)
    {
        $res = FolderItem::create($res);        
        $this->assertEquals($readableByAnonymous, $this->acl->isReadableByAnonymous($res));
    }

    /**
     * @test
     * @dataProvider provideFiles
     */
    public function setReadableByAnonymousShouldReverseReadableByAnonymousResultsForFiles($res, $readable, $writeable, $readableByAnonymous)
    {
        $acl = new SimpleAcl(false);
                
        $this->assertEquals(false, $acl->isReadableByAnonymous($res));
        $this->assertEquals($readable, $acl->isReadable($res));
        $this->assertEquals($writeable, $acl->isWriteable($res));
        
    }
    
    /**
     * @test
     * @dataProvider provideFolders
     */
    public function setReadableByAnonymousShouldReverseReadableByAnonymousResultsForFolders($res, $readable, $writeable, $readableByAnonymous)
    {
        $acl = new SimpleAcl(false);
                
        $this->assertEquals(false, $acl->isReadableByAnonymous($res));
        $this->assertEquals($readable, $acl->isReadable($res));
        $this->assertEquals($writeable, $acl->isWriteable($res));
        
    }
    
    
    
}
