<?php

namespace Xi\Filelib\Tests\File;

use DateTime;
use Xi\Filelib\File\File;
use Xi\Filelib\Version;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Tests\BaseVersionableTestCase;

class FileTest extends BaseVersionableTestCase
{
    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\File'));
    }

    public function getClassName()
    {
        return 'Xi\Filelib\File\File';
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

        $resource->expects($this->once())->method('getMimetype')->will($this->returnValue('lus/tus'));
        $resource->expects($this->once())->method('getSize')->will($this->returnValue(666));

        $this->assertEquals('lus/tus', $file->getMimetype());
        $this->assertEquals(666, $file->getSize());

    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $file = File::create();

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

        $val = new DateTime('1978-01-02');
        $this->assertInstanceOf('DateTime', $file->getDateCreated());
        $this->assertSame($file, $file->setDateCreated($val));
        $this->assertSame($val, $file->getDateCreated());

        $val = 1;
        $this->assertNull($file->getStatus());
        $this->assertSame($file, $file->setStatus($val));
        $this->assertEquals($val, $file->getStatus());

        $val = Resource::create();
        $this->assertNull($file->getResource());
        $this->assertSame($file, $file->setResource($val));
        $this->assertSame($val, $file->getResource());

        $val = 'uuid-uuid-uuid';
        $this->assertNull($file->getUuid());
        $this->assertSame($file, $file->setUuid($val));
        $this->assertEquals($val, $file->getUuid());

        $val = array('lussen', 'le', 'tussen');
        $this->assertEquals(array(), $file->getVersions());
        foreach ($val as $version) {
            $this->assertSame($file, $file->addVersion(Version::get($version)));
        }
        $this->assertEquals($val, $file->getVersions());

    }

    /**
     * @test
     */
    public function createInitializesValues()
    {
        $data = array(
            'id' => 'lussen-id',
            'folder_id' => 'lussen-folder',
            'profile' => 'lussen-profile',
            'name' => 'lussen-name',
            'date_created' => new DateTime('1978-03-21'),
            'status' => File::STATUS_COMPLETED,
            'resource' => Resource::create(),
            'uuid' => 'lussen-uuid',
            'data' => array(
                'versions' => array('tussen'),
                'tenhunen' => 'on suurista suurin'
            )
        );

        $file = File::create($data);

        $this->assertEquals($data['id'], $file->getId());
        $this->assertEquals($data['folder_id'], $file->getFolderId());
        $this->assertEquals($data['profile'], $file->getProfile());
        $this->assertEquals($data['name'], $file->getName());
        $this->assertSame($data['date_created'], $file->getDateCreated());
        $this->assertEquals($data['status'], $file->getStatus());
        $this->assertSame($data['resource'], $file->getResource());
        $this->assertEquals($data['uuid'], $file->getUuid());
        $this->assertEquals($data['data'], $file->getData()->toArray());
    }

    /**
     * @test
     */
    public function toArrayShouldWorkAsExpected()
    {
        $dt = new \DateTime('1978-03-21');
        
        $resource = Resource::create();

        $file = File::create();
        $file->setId(1);
        $file->setFolderId(655);
        $file->setProfile('unknown');
        $file->setName('kukkuu.png');
        $file->setDateCreated($dt);
        $file->setStatus(54);
        $file->setUuid('tussi-poski');
        $file->setResource($resource);

        $file->addVersion(Version::get('lussi'));
        $file->addVersion(Version::get('xussi'));

        $this->assertEquals($file->toArray(), array(
            'id' => 1,
            'folder_id' => 655,
            'profile' => 'unknown',
            'name' => 'kukkuu.png',
            'date_created' => new \DateTime('1978-03-21'),
            'status' => 54,
            'uuid' => 'tussi-poski',
            'resource' => $resource,
            'data' => array('versions' => array('lussi', 'xussi'))
        ));
    }

    /**
     * @test
     */
    public function createShouldCreateNewInstance()
    {
        $this->assertInstanceOf('Xi\Filelib\File\File', File::create(array()));
    }
}
