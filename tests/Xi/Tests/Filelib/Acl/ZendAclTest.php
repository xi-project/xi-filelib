<?php

namespace Xi\Tests\Filelib\Acl;

use \PHPUnit_Framework_TestCase as TestCase;

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\File\File;

use Xi\Filelib\Acl\Acl,
    Xi\Filelib\Acl\ZendAcl,
    \Zend_Acl
    ;

class ZendAclTest extends \Xi\Tests\Filelib\TestCase
{
    
    /**
     *
     * @var ZendAcl
     */
    private $acl;
    
    
    public function setUp()
    {
        if (!class_exists('\Zend_Acl')) {
            $this->markTestSkipped("Zend Acl could not be loaded");
        }
        
        $this->acl = new ZendAcl(true);
        
        
        $zacl = new Zend_Acl();
        
        $zacl->addRole('pekkis');
        $zacl->addRole('anonymous');
        
        $zacl->deny('pekkis');
        $zacl->deny('anonymous');
                
        $zacl->addResource('Xi_Filelib_Folder_1');
        $zacl->addResource('Xi_Filelib_Folder_2');
        
        
        $zacl->addResource('Xi_Filelib_File_1');
        $zacl->addResource('Xi_Filelib_File_2');
        $zacl->addResource('Xi_Filelib_File_3');

        
        $zacl->allow('pekkis', 'Xi_Filelib_Folder_1');
        $zacl->allow('pekkis', 'Xi_Filelib_Folder_2', 'read');
        
        
        $zacl->allow('pekkis', 'Xi_Filelib_File_2');
        $zacl->allow('pekkis', 'Xi_Filelib_File_3', 'read');
        
        $zacl->allow('anonymous', 'Xi_Filelib_File_3');
        
        
        $this->acl->setRole('pekkis');
        $this->acl->setAnonymousRole('anonymous');
        $this->acl->setAcl($zacl);
        
    }
    
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Acl\ZendAcl'));
        $this->assertContains('Xi\Filelib\Acl\Acl', class_implements('Xi\Filelib\Acl\ZendAcl'));
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
                false,
            ),
            array(
                array(
                    'id' => 2,
                    'parent_id' => 1,
                    'url' => 'lussuttaja/tussi',
                    'name' => 'tussi',
                ),
                true,
                false,
                false
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
                false,
                false,
                false
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
                false
            ),

            array(
                array(
                   'id' => 3,
                    'folder_id' => 3,
                    'url' => 'tohtori-tussi.png',
                    'name' => 'tohtori-tussi.png',
                ),
                true,
                false,
                true
            ),
            
        );
        
    }
    
    /**
     * @test
     */
    public function settersAndGettersShouldGetAndSet()
    {
        $acl = new ZendAcl();
        
        $this->assertNull($acl->getAcl());
        $this->assertNull($acl->getRole());
        $this->assertNull($acl->getAnonymousRole());
        
        
        $zacl = new Zend_Acl();
        $role = "tussi";
        $anonymousRole = 'loso';
        
        $this->assertSame($acl, $acl->setAcl($zacl));
        $this->assertSame($acl, $acl->setRole($role));
        $this->assertSame($acl, $acl->setAnonymousRole($anonymousRole));
                
        $this->assertEquals($zacl, $acl->getAcl());
        $this->assertEquals($role, $acl->getRole());
        $this->assertEquals($anonymousRole, $acl->getAnonymousRole());
        
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
     */
    public function getResourceIdentifierShouldReturnCorrectIdentifiers()
    {
        $folder = Folder::create(array(
            'id' => 4,
            'parent_id' => 6,
            'name' => 'lussutus',
            'url' => 'tussutus'
        ));

        $folder2 = Folder::create(array(
            'id' => 6,
            'parent_id' => 6,
            'name' => 'lussutus',
            'url' => 'tussutus'
        ));

        
        
        $file = File::create(array(
            'id' => 272,
        ));
        
        
        $file2 = File::create(array(
            'id' => 276,
        ));
        
        
        $this->assertEquals('Xi_Filelib_File_272', $this->acl->getResourceIdentifier($file));
        $this->assertEquals('Xi_Filelib_File_276', $this->acl->getResourceIdentifier($file2));
        
        $this->assertEquals('Xi_Filelib_Folder_4', $this->acl->getResourceIdentifier($folder));
        $this->assertEquals('Xi_Filelib_Folder_6', $this->acl->getResourceIdentifier($folder2));
        
    }
    
    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getResourceIdentifierShouldThrowExceptionForNonIdentifiableObjects()
    {
        $diterator = new \DirectoryIterator(ROOT_TESTS);
        
        return $this->acl->getResourceIdentifier($diterator);
        
    }

    
    
    
    
    
}
