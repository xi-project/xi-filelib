<?php

namespace Xi\Tests\Filelib\Backend\Doctrine2\Entity;

use Xi\Filelib\Backend\Doctrine2\Entity\File;
use Xi\Filelib\Backend\Doctrine2\Entity\Folder;
use DateTime;

class FileTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Backend\Doctrine2\Entity\File'));
    }
    
    
    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $file = new File();

        $this->assertNull($file->getId());

        $value = 'mime/type';
        $this->assertNull($file->getMimeType());
        $this->assertSame($file, $file->setMimeType($value));
        $this->assertEquals($value, $file->getMimeType());

        $value = 'profile';
        $this->assertNull($file->getProfile());
        $this->assertSame($file, $file->setProfile($value));
        $this->assertEquals($value, $file->getProfile());

        $value = 500000;
        $this->assertNull($file->getSize());
        $this->assertSame($file, $file->setSize($value));
        $this->assertEquals($value, $file->getSize());
        
        $value = 'xooxer';
        $this->assertNull($file->getName());
        $this->assertSame($file, $file->setName($value));
        $this->assertEquals($value, $file->getName());
        
        $value = 'linkster';
        $this->assertNull($file->getLink());
        $this->assertSame($file, $file->setLink($value));
        $this->assertEquals($value, $file->getLink());

        $value = 52;
        $this->assertNull($file->getStatus());
        $this->assertSame($file, $file->setStatus($value));
        $this->assertEquals($value, $file->getStatus());

        $value = new DateTime('2011-04-05');
        $this->assertNull($file->getDateUploaded());
        $this->assertSame($file, $file->setDateUploaded($value));
        $this->assertSame($value, $file->getDateUploaded());
        
        $value = new Folder();
        $this->assertNull($file->getFolder());
        $this->assertSame($file, $file->setFolder($value));
        $this->assertSame($value, $file->getFolder());
        
    }

    
}