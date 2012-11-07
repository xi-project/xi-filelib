<?php

namespace Xi\Tests\Filelib\Acl;

use \PHPUnit_Framework_TestCase as TestCase;

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;
use Xi\Filelib\Acl\Acl;
use Xi\Filelib\Acl\SimpleAcl;

class SimpleAclTest extends \Xi\Tests\Filelib\TestCase
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
   
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Acl\SimpleAcl'));
        $this->assertContains('Xi\Filelib\Acl\Acl', class_implements('Xi\Filelib\Acl\SimpleAcl'));
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
    public function isReadableShouldReturnExpectedResultsForFiles($res, $readable, $writable, $readableByAnonymous)
    {
        $res = File::create($res);        
        $this->assertEquals($readable, $this->acl->isFileReadable($res));
    }
    
    
    /**
     * @test
     * @dataProvider provideFolders
     */
    public function IsReadableShouldReturnExpectedResultForFolders($res, $readable, $writable, $readableByAnonymous)
    {
        $res = Folder::create($res);        
        $this->assertEquals($readable, $this->acl->isFolderReadable($res));
    }

    
    /**
     * @test
     * @dataProvider provideFiles
     */
    public function isWritableShouldReturnExpectedResultsForFiles($res, $readable, $writable, $readableByAnonymous)
    {
        $res = File::create($res);        
        $this->assertEquals($writable, $this->acl->isFileWritable($res));
    }
    
    
    /**
     * @test
     * @dataProvider provideFolders
     */
    public function IsWritableShouldReturnExpectedResultForFolders($res, $readable, $writable, $readableByAnonymous)
    {
        $res = Folder::create($res);        
        $this->assertEquals($writable, $this->acl->isFolderWritable($res));
    }
    
    
    /**
     * @test
     * @dataProvider provideFiles
     */
    public function isAnonymousReadableShouldReturnExpectedResultsForFiles($res, $readable, $writable, $readableByAnonymous)
    {
        $res = File::create($res);        
        $this->assertEquals($readableByAnonymous, $this->acl->isFileReadableByAnonymous($res));
    }
    
    
    /**
     * @test
     * @dataProvider provideFolders
     */
    public function IsAnonymousReadableShouldReturnExpectedResultForFolders($res, $readable, $writable, $readableByAnonymous)
    {
        $res = Folder::create($res);        
        $this->assertEquals($readableByAnonymous, $this->acl->isFolderReadableByAnonymous($res));
    }

    /**
     * @test
     * @dataProvider provideFiles
     */
    public function setReadableByAnonymousShouldReverseReadableByAnonymousResultsForFiles($res, $readable, $writable, $readableByAnonymous)
    {
        $res = File::create($res);        
        
        $acl = new SimpleAcl(false);
                
        $this->assertEquals(false, $acl->isFileReadableByAnonymous($res));
        $this->assertEquals($readable, $acl->isFileReadable($res));
        $this->assertEquals($writable, $acl->isFileWritable($res));
        
    }
    
    /**
     * @test
     * @dataProvider provideFolders
     */
    public function setReadableByAnonymousShouldReverseReadableByAnonymousResultsForFolders($res, $readable, $writable, $readableByAnonymous)
    {
        $res = Folder::create($res);        
        
        $acl = new SimpleAcl(false);
                
        $this->assertEquals(false, $acl->isFolderReadableByAnonymous($res));
        $this->assertEquals($readable, $acl->isFolderReadable($res));
        $this->assertEquals($writable, $acl->isFolderWritable($res));
        
    }
    
    
    
}
