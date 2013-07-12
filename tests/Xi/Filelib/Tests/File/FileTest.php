<?php

namespace Xi\Filelib\Tests\File;

use Xi\Filelib\FileLibrary;
use DateTime;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;

class FileTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\File'));
    }

    /**
     * @test
     */
    public function getSizeAndGetMimeTypeShouldBeDelegatedToResource()
    {
        $resource = $this->getMockedResource();

        $file = File::create(array(
            'resource' => $resource,
        ));

        $resource->expects($this->once())->method('getMimetype');
        $resource->expects($this->once())->method('getSize');

        $ret = $file->getMimetype();
        $ret = $file->getSize();

    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $file = new File();

        $val = 666;
        $this->assertNull($file->getId());
        $this->assertSame($file, $file->setId($val));
        $this->assertEquals($val, $file->getId());

        $val = 'image/lus';
        $this->assertNull($file->getFolderId());
        $this->assertSame($file, $file->setFolderId($val));
        $this->assertEquals($val, $file->getFolderId());

        $val = 'lamanmeister';
        $this->assertNull($file->getProfile());
        $this->assertSame($file, $file->setProfile($val));
        $this->assertEquals($val, $file->getProfile());

        $val = 'lamanmeister.xoo';
        $this->assertNull($file->getName());
        $this->assertSame($file, $file->setName($val));
        $this->assertEquals($val, $file->getName());

        $val = 'linkster';
        $this->assertNull($file->getLink());
        $this->assertSame($file, $file->setLink($val));
        $this->assertEquals($val, $file->getLink());

        $val = new DateTime('1978-01-02');
        $this->assertNull($file->getDateCreated());
        $this->assertSame($file, $file->setDateCreated($val));
        $this->assertSame($val, $file->getDateCreated());

        $val = 1;
        $this->assertNull($file->getStatus());
        $this->assertSame($file, $file->setStatus($val));
        $this->assertEquals($val, $file->getStatus());

        $val = new Resource();
        $this->assertNull($file->getResource());
        $this->assertSame($file, $file->setResource($val));
        $this->assertSame($val, $file->getResource());

        $val = 'uuid-uuid-uuid';
        $this->assertNull($file->getUuid());
        $this->assertSame($file, $file->setUuid($val));
        $this->assertEquals($val, $file->getUuid());

        $val = array('lussen', 'le', 'tussen');
        $this->assertEquals(array(), $file->getVersions());
        $this->assertSame($file, $file->setVersions($val));
        $this->assertEquals($val, $file->getVersions());

    }

    /**
     * @return array
     */
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
                    'date_created' => new \DateTime('2010-01-01 01:01:01'),
                    'status' => 8,
                    'uuid' => 'uuid-uuid',
                    'resource' => new Resource(),
                    'versions' => array('watussi', 'lussi')
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
        $file = new File();
        $file->fromArray($data);

        $map = array(
            'id' => 'getId',
            'folder_id' => 'getFolderId',
            'profile' => 'getProfile',
            'name' => 'getName',
            'link' => 'getLink',
            'date_created' => 'getDateCreated',
            'status' => 'getStatus',
            'resource' => 'getResource',
            'uuid' => 'getUuid',
            'versions' => 'getVersions'
        );

        foreach ($map as $key => $method) {
            if (isset($data[$key])) {
                $this->assertEquals($data[$key], $file->$method());
            } else {

                if ($key == 'versions') {
                    $this->assertEquals(array(), $file->$method());
                } else {
                    $this->assertNull($file->$method());
                }
            }
        }

    }

    /**
     * @test
     */
    public function toArrayShouldWorkAsExpected()
    {
        $file = new File();
        $file->setId(1);
        $file->setFolderId(655);
        $file->setProfile('unknown');
        $file->setName('kukkuu.png');
        $file->setLink('linksor');
        $file->setDateCreated(new \DateTime('1978-03-21'));
        $file->setStatus(54);
        $file->setUuid('tussi-poski');
        $file->setResource(new Resource());
        $file->setVersions(array('lussi', 'xussi'));

        $this->assertEquals($file->toArray(), array(
            'id' => 1,
            'folder_id' => 655,
            'profile' => 'unknown',
            'name' => 'kukkuu.png',
            'link' => 'linksor',
            'date_created' => new \DateTime('1978-03-21'),
            'status' => 54,
            'uuid' => 'tussi-poski',
            'resource' => new Resource(),
            'versions' => array('lussi', 'xussi')
        ));

        $file = new File();
        $this->assertEquals($file->toArray(), array(
            'id' => null,
            'folder_id' => null,
            'profile' => null,
            'name' => null,
            'link' => null,
            'date_created' => null,
            'status' => null,
            'uuid' => null,
            'resource' => null,
            'versions' => array(),
        ));

    }

    /**
     * @test
     */
    public function createShouldCreateNewInstance()
    {
        $this->assertInstanceOf('Xi\Filelib\File\File', File::create(array()));
    }

    /**
     * @test
     */
    public function getDataShouldReturnACachedArrayObject()
    {
        $file = new File();
        $data = $file->getData();

        $this->assertInstanceOf('ArrayObject', $data);

        $data['tussi'] = 'lussi';

        $this->assertSame($data, $file->getData());

    }

    /**
     * @test
     */
    public function addVersionShouldAddVersion()
    {
        $file = File::create(array('versions' => array('tussi', 'watussi')));
        $file->addVersion('lussi');

        $this->assertEquals(array('tussi', 'watussi', 'lussi'), $file->getVersions());
    }

    /**
     * @test
     */
    public function addVersionShouldNotAddVersionIfVersionExists()
    {
        $file = File::create(array('versions' => array('tussi', 'watussi')));
        $file->addVersion('watussi');

        $this->assertEquals(array('tussi', 'watussi'), $file->getVersions());
    }

    /**
     * @test
     */
    public function removeVersionShouldRemoveVersion()
    {
        $file = File::create(array('versions' => array('tussi', 'watussi')));
        $file->removeVersion('watussi');

        $this->assertEquals(array('tussi'), $file->getVersions());
    }

    /**
     * @test
     */
    public function hasVersionShouldReturnWhetherResourceHasVersion()
    {
        $file = File::create(array('versions' => array('tussi', 'watussi')));

        $this->assertTrue($file->hasVersion('tussi'));
        $this->assertTrue($file->hasVersion('watussi'));
        $this->assertFalse($file->hasVersion('lussi'));
    }

}
