<?php

namespace Xi\Filelib\Tests\Folder;

use Xi\Filelib\Folder\Folder;

class FolderTest extends \Xi\Filelib\Tests\TestCase
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
     * @dataProvider fromArrayProvider
     * @test
     */
    public function fromArrayShouldWorkAsExpected($data)
    {
        $folder = Folder::create($data);

        $map = array(
            'id' => 'getId',
            'parent_id' => 'getParentId',
            'name' => 'getName',
            'url' => 'getUrl',
            'uuid' => 'getUuid',
        );

        foreach ($map as $key => $method) {
            if (isset($data[$key])) {

                if ($key === 'data') {
                    $this->assertEquals($data[$key], $folder->getData()->toArray());
                } else {
                    $this->assertEquals($data[$key], $folder->$method());
                }

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

    /**
     * @test
     */
    public function createShouldCreateNewInstance()
    {
        $this->assertInstanceOf('Xi\Filelib\Folder\Folder', Folder::create(array()));
    }

    /**
     * @test
     */
    public function clonesDeeply()
    {
        $source = Folder::create();
        $sourceData = $source->getData();
        $sourceData->set('lussutappa', 'tussia');

        $target = clone $source;
        $targetData = $target->getData();

        $this->assertEquals($source->getData()->toArray(), $target->getData()->toArray());
        $this->assertNotSame($sourceData, $targetData);
    }


}
