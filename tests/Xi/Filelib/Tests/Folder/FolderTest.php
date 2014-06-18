<?php

namespace Xi\Filelib\Tests\Folder;

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Tests\BaseIdentifiableTestCase;

class FolderTest extends BaseIdentifiableTestCase
{
    /**
     * @test
     */
    public function getClassName()
    {
        return 'Xi\Filelib\Folder\Folder';
    }

        /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $folder = Folder::create();

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
                    'data' => array(
                        'lussen' => 'zu tussen',
                    )
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
     * @test
     */
    public function createInitializesValues()
    {
        $data = array(
            'id' => 1,
            'parent_id' => 1,
            'name' => 'puuppa.jpg',
            'url' => 'lussenhoff',
            'uuid' => 'uuid-lusser',
            'data' => array(
                'lussen' => 'zu tussen',
            )
        );

        $folder = Folder::create($data);
        $this->assertEquals($data['id'], $folder->getId());
        $this->assertEquals($data['parent_id'], $folder->getParentId());
        $this->assertEquals($data['url'], $folder->getUrl());
        $this->assertEquals($data['name'], $folder->getName());
        $this->assertEquals($data['uuid'], $folder->getUuid());
        $this->assertEquals($data['data'], $folder->getData()->toArray());
    }

    /**
     * @test
     */
    public function toArrayShouldWorkAsExpected()
    {
        $folder = Folder::create();
        $folder->setId(1);
        $folder->setParentId(655);
        $folder->setName('klussutusta');
        $folder->setUrl('/lussen/hofen');
        $folder->setUuid('luss3r');
        $folder->setData(array(
            'tenhusta suurempi' => 'on vain tenhunen'
        ));

        $this->assertEquals($folder->toArray(), array(
            'id' => 1,
            'parent_id' => 655,
            'name' => 'klussutusta',
            'url' => '/lussen/hofen',
            'uuid' => 'luss3r',
            'data' => array(
                'tenhusta suurempi' => 'on vain tenhunen'
            )
        ));

        $folder = Folder::create();
        $this->assertEquals($folder->toArray(), array(
            'id' => null,
            'parent_id' => null,
            'name' => null,
            'url' => null,
            'uuid' => null,
            'data' => array()
        ));

    }

}
