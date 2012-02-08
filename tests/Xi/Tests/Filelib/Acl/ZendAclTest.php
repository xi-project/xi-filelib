<?php

namespace Xi\Tests\Filelib\Acl;

use \PHPUnit_Framework_TestCase as TestCase;

use Xi\Filelib\Folder\FolderItem;
use Xi\Filelib\File\FileItem;

use Xi\Filelib\Acl\Acl,
    Xi\Filelib\Acl\ZendAcl,
    \Zend_Acl
    ;

class ZendAclTest extends TestCase
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
        
        $acl->setAcl($zacl);
        $acl->setRole($role);
        $acl->setAnonymousRole($anonymousRole);
                
        $this->assertEquals($zacl, $acl->getAcl());
        $this->assertEquals($role, $acl->getRole());
        $this->assertEquals($anonymousRole, $acl->getAnonymousRole());
        
        
        
        
    }
    
    
    
    /**
     * @test
     */
    public function getResourceIdentifierShouldReturnCorrectIdentifiers()
    {
        $folder = FolderItem::create(array(
            'id' => 4,
            'parent_id' => 6,
            'name' => 'lussutus',
            'url' => 'tussutus'
        ));

        $folder2 = FolderItem::create(array(
            'id' => 6,
            'parent_id' => 6,
            'name' => 'lussutus',
            'url' => 'tussutus'
        ));

        
        
        $file = FileItem::create(array(
            'id' => 272,
        ));
        
        
        $file2 = FileItem::create(array(
            'id' => 276,
        ));
        
        
        $this->assertEquals('Xi_Filelib_File_272', $this->acl->getResourceIdentifier($file));
        $this->assertEquals('Xi_Filelib_File_276', $this->acl->getResourceIdentifier($file2));
        
        $this->assertEquals('Xi_Filelib_Folder_4', $this->acl->getResourceIdentifier($folder));
        $this->assertEquals('Xi_Filelib_Folder_6', $this->acl->getResourceIdentifier($folder2));
        
    }
    
    
    public function getResourceIdentifierShouldThrowExceptionForNonIdentifiableObjects()
    {
        
        
        
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
    
    
}
