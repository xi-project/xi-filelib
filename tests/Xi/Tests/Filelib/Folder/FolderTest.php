<?php

namespace Xi\Tests\Filelib\Folder;

use Xi\Filelib\Folder\Folder;

class FolderTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Folder\Folder'));
    }


        /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $folder = new Folder();

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');

        $val = 666;
        $this->assertEquals(null, $folder->getId());
        $this->assertSame($folder, $folder->setId($val));
        $this->assertEquals($val, $folder->getId());

        $val = 555;
        $this->assertEquals(null, $folder->getParentId());
        $this->assertSame($folder, $folder->setParentId($val));
        $this->assertEquals($val, $folder->getParentId());

        $val = 'lamanmeister';
        $this->assertEquals(null, $folder->getName());
        $this->assertSame($folder, $folder->setName($val));
        $this->assertEquals($val, $folder->getName());

        $val = 'urlster';
        $this->assertEquals(null, $folder->getUrl());
        $this->assertSame($folder, $folder->setUrl($val));
        $this->assertEquals($val, $folder->getUrl());

        $val = 'uuid';
        $this->assertEquals(null, $folder->getUuid());
        $this->assertSame($folder, $folder->setUuid($val));
        $this->assertEquals($val, $folder->getUuid());

    }


    public function fromArrayProvider()
    {
        return array(
            array(
                array(
                    'id' => 1,
                    'parent_id' => 1,
                    'name' => 'puuppa.jpg',
                    'url' => 'lussenhoff',
                    'uuid' => 'uuid-lusser',
                ),
            ),
            array(
                array(
                    'url' => 'lussenhoff',
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
        $folder = new Folder();
        $folder->fromArray($data);

        $map = array(
            'id' => 'getId',
            'parent_id' => 'getParentId',
            'name' => 'getName',
            'url' => 'getUrl',
            'uuid' => 'getUuid',
        );

        foreach($map as $key => $method) {
            if(isset($data[$key])) {
                $this->assertEquals($data[$key], $folder->$method());
            } else {
                $this->assertNull($folder->$method());
            }
        }

    }

    /**
     * @test
     */
    public function toArrayShouldWorkAsExpected()
    {
        $folder = new Folder();
        $folder->setId(1);
        $folder->setParentId(655);
        $folder->setName('klussutusta');
        $folder->setUrl('/lussen/hofen');
        $folder->setUuid('luss3r');

        $this->assertEquals($folder->toArray(), array(
            'id' => 1,
            'parent_id' => 655,
            'name' => 'klussutusta',
            'url' => '/lussen/hofen',
            'uuid' => 'luss3r',
        ));


        $folder = new Folder();
        $this->assertEquals($folder->toArray(), array(
            'id' => null,
            'parent_id' => null,
            'name' => null,
            'url' => null,
            'uuid' => null,
        ));

    }

    /**
     * @test
     */
    public function createShouldCreateNewInstance()
    {
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', Folder::create(array()));
    }



}