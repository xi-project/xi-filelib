<?php

namespace Xi\Tests\Filelib\File;

use DateTime;

class FileItemTest extends \PHPUnit_Framework_TestCase
{
    
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
     */
    public function testFromArray($data)
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
            'date_uploaded' => 'getDateUploaded'
        );
        
        foreach($map as $key => $method) {
            if(isset($data[$key])) {
                $this->assertEquals($data[$key], $file->$method());    
            } else {
                $this->assertNull($file->$method());
            }
        }
        
    }
        
    public function testToArray()
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
        
        
        $this->assertEquals($file->toArray(), array(
            'id' => 1,
            'folder_id' => 655,
            'mimetype' => 'tussi/lussutus',
            'profile' => 'unknown',
            'size' => 123456,
            'name' => 'kukkuu.png',
            'link' => 'linksor',
            'date_uploaded' => new \DateTime('1978-03-21')
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
        ));
        
        
    }
    
    
    public function testFilelibSetAndGet()
    {

        $lusser = $this->getMock('\Xi\Filelib\Storage\Storage');

        
        $conf = new \Xi\Filelib\Configuration();
        
        $conf->setStorage($lusser);
        
                
        
        
        
        
        
    }
    
    
    
    
}