<?php

namespace Xi\Tests\Filelib\Backend\Platform\DoctrineOrm\Entity;

use Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\Folder;

class FolderTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Backend\Platform\DoctrineOrm\Entity\Folder'));
    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $folder = new Folder();

        $value = 503;
        $this->assertNull($folder->getId());

        $value = 'xooxer';
        $this->assertNull($folder->getName());
        $this->assertSame($folder, $folder->setName($value));
        $this->assertEquals($value, $folder->getName());

        $value = 'url';
        $this->assertNull($folder->getUrl());
        $this->assertSame($folder, $folder->setUrl($value));
        $this->assertEquals($value, $folder->getUrl());

        $value = new Folder();
        $this->assertNull($folder->getParent());
        $this->assertSame($folder, $folder->setParent($value));
        $this->assertSame($value, $folder->getParent());

        $folder->removeParent();
        $this->assertNull($folder->getParent());

    }



}
