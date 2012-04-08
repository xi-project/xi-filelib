<?php

namespace Xi\Tests\Filelib\File;

use Xi\Filelib\FileLibrary,
    DateTime,
    Xi\Filelib\File\FileItem;

class FileItemTest extends \PHPUnit_Framework_TestCase
{
    
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\FileItem'));
        $this->assertContains('Xi\Filelib\File\File', class_implements('Xi\Filelib\File\FileItem'));
    }
    
    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $file = new FileItem();
        
        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        
        $val = 666;
        $this->assertNull($file->getId());
        $this->assertSame($file, $file->setId($val));
        $this->assertEquals($val, $file->getId());

        $val = 'image/lus';
        $this->assertNull($file->getFolderId());
        $this->assertSame($file, $file->setFolderId($val));
        $this->assertEquals($val, $file->getFolderId());

        $val = 'image/lus';
        $this->assertNull($file->getMimetype());
        $this->assertSame($file, $file->setMimetype($val));
        $this->assertEquals($val, $file->getMimetype());

        $val = 'lamanmeister';
        $this->assertNull($file->getProfile());
        $this->assertSame($file, $file->setProfile($val));
        $this->assertEquals($val, $file->getProfile());

        $val = 64643;
        $this->assertNull($file->getSize());
        $this->assertSame($file, $file->setSize($val));
        $this->assertEquals($val, $file->getSize());

        $val = 'lamanmeister.xoo';
        $this->assertNull($file->getName());
        $this->assertSame($file, $file->setName($val));
        $this->assertEquals($val, $file->getName());

        $val = 'linkster';
        $this->assertNull($file->getLink());
        $this->assertSame($file, $file->setLink($val));
        $this->assertEquals($val, $file->getLink());

        $val = new DateTime('1978-01-02');
        $this->assertNull($file->getDateUploaded());
        $this->assertSame($file, $file->setDateUploaded($val));
        $this->assertSame($val, $file->getDateUploaded());
        
        
        $val = 1;
        $this->assertNull($file->getStatus());
        $this->assertSame($file, $file->setStatus($val));
        $this->assertEquals($val, $file->getStatus());

        
        
    }
    
    public function fromArrayProvider()
    {
        return array(
            array(
                array(
                    'id' => 1,
                    'folder_id' => 1,
                    'mimetype' => 'image/jpeg',
                    'profile' => 'default',
                    'size' => 600,
                    'name' => 'puuppa.jpg',
                    'link' => 'lussenhoff',
                    'date_uploaded' => new \DateTime('2010-01-01 01:01:01'),
                    'status' => 8,
                ),         
            ),
            array(
                array(
                    'link' => 'lussenhoff',
                ),         
            ),
        
        );
        
        
    }
    
    /**
     * @dataProvider fromArrayProvider
     * @test
     */
    public function fromArrayShouldWorkAsExpected($data)
    {
        $file = new \Xi\Filelib\File\FileItem();
        $file->fromArray($data);

        $map = array(
            'id' => 'getId',
            'folder_id' => 'getFolderId',
            'mimetype' => 'getMimeType',
            'profile' => 'getProfile',
            'size' => 'getSize',
            'name' => 'getName',
            'link' => 'getLink',
            'date_uploaded' => 'getDateUploaded',
            'status' => 'getStatus',
        );
        
        foreach($map as $key => $method) {
            if(isset($data[$key])) {
                $this->assertEquals($data[$key], $file->$method());    
            } else {
                $this->assertNull($file->$method());
            }
        }
        
    }
    
    /**
     * @test
     */
    public function toArrayShouldWorkAsExpected()
    {
        $file = new \Xi\Filelib\File\FileItem();
        $file->setId(1);
        $file->setFolderId(655);
        $file->setMimeType('tussi/lussutus');
        $file->setProfile('unknown');
        $file->setSize(123456);
        $file->setName('kukkuu.png');
        $file->setLink('linksor');
        $file->setDateUploaded(new \DateTime('1978-03-21'));
        $file->setStatus(54);
                
        $this->assertEquals($file->toArray(), array(
            'id' => 1,
            'folder_id' => 655,
            'mimetype' => 'tussi/lussutus',
            'profile' => 'unknown',
            'size' => 123456,
            'name' => 'kukkuu.png',
            'link' => 'linksor',
            'date_uploaded' => new \DateTime('1978-03-21'),
            'status' => 54,
        ));

        
        $file = new \Xi\Filelib\File\FileItem();
        $this->assertEquals($file->toArray(), array(
            'id' => null,
            'folder_id' => null,
            'mimetype' => null,
            'profile' => null,
            'size' => null,
            'name' => null,
            'link' => null,
            'date_uploaded' => null,
            'status' => null,
        ));
        
        
    }
    
    /**
     * @test
     */
    public function createShouldCreateNewInstance()
    {
        $this->assertInstanceOf('Xi\Filelib\File\FileItem', FileItem::create(array()));
    }
    
    
    
}