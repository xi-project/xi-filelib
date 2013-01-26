<?php

namespace Xi\Filelib\Tests\File;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\FileProfile;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Event\PluginEvent;

class FileProfileTest extends \Xi\Filelib\Tests\TestCase
{


    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\File\FileProfile'));
        $this->assertContains(
            'Symfony\Component\EventDispatcher\EventSubscriberInterface', class_implements('Xi\Filelib\File\FileProfile')
        );
    }

    /**
     * @test
     */
    public function classShouldSubscribeToCorrectEvents()
    {
        $events = FileProfile::getSubscribedEvents();
        $this->assertArrayHasKey('xi_filelib.plugin.add', $events);
    }

    /**
     * @test
     */
    public function onPluginAddShouldCallAddPluginIfPluginHasProfile()
    {
        $profile = $this->getMockBuilder('Xi\Filelib\File\FileProfile')
                     ->setMethods(array('addPlugin'))
                     ->getMock();
        $profile->setIdentifier('lussen');

        $plugin = $this->getMock('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->atLeastOnce())->method('getProfiles')->will($this->returnValue(array('lussen', 'hofer')));

        $profile->expects($this->once())->method('addPlugin')->with($this->equalTo($plugin));

        $event = new PluginEvent($plugin);

        $profile->onPluginAdd($event);

    }

    /**
     * @test
     */
    public function onPluginAddShouldNotCallAddPluginIfPluginDoesNotHaveProfile()
    {
        $profile = $this->getMockBuilder('Xi\Filelib\File\FileProfile')
                     ->setMethods(array('addPlugin'))
                     ->getMock();
        $profile->setIdentifier('non-existing-profile');

        $plugin = $this->getMock('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->atLeastOnce())->method('getProfiles')->will($this->returnValue(array('lussen', 'hofer')));

        $profile->expects($this->never())->method('addPlugin');

        $event = new PluginEvent($plugin);
        $profile->onPluginAdd($event);
    }




    /**
     * @test
     */
    public function gettersAndSettersShouldWorkAsExpected()
    {
        $profile = new FileProfile();

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $this->assertEquals(null, $profile->getFilelib());
        $this->assertSame($profile, $profile->setFilelib($filelib));
        $this->assertSame($filelib, $profile->getFilelib());

        $linker = $this->getMockForAbstractClass('Xi\Filelib\Linker\Linker');
        $this->assertEquals(null, $profile->getLinker());
        $this->assertSame($profile, $profile->setLinker($linker));
        $this->assertSame($linker, $profile->getLinker());

        $val = 'Descriptione';
        $this->assertEquals(null, $profile->getDescription());
        $this->assertSame($profile, $profile->setDescription($val));
        $this->assertEquals($val, $profile->getDescription());

        $val = 'Identifiere';
        $this->assertEquals(null, $profile->getIdentifier());
        $this->assertSame($profile, $profile->setIdentifier($val));
        $this->assertEquals($val, $profile->getIdentifier());


        $val = true;
        $this->assertEquals(true, $profile->getAccessToOriginal());
        $this->assertSame($profile, $profile->setAccessToOriginal($val));
        $this->assertEquals($val, $profile->getAccessToOriginal());

        $val = false;
        $this->assertEquals(true, $profile->getPublishOriginal());
        $this->assertSame($profile, $profile->setPublishOriginal($val));
        $this->assertEquals($val, $profile->getPublishOriginal());


        /*
        $val = 666;
        $this->assertEquals(null, $profile->getId());
        $this->assertSame($profile, $profile->setId($val));
        $this->assertEquals($val, $profile->getId());

        $val = 'image/lus';
        $this->assertEquals(null, $profile->getFolderId());
        $this->assertSame($profile, $profile->setFolderId($val));
        $this->assertEquals($val, $profile->getFolderId());

        $val = 'image/lus';
        $this->assertEquals(null, $profile->getMimetype());
        $this->assertSame($profile, $profile->setMimetype($val));
        $this->assertEquals($val, $profile->getMimetype());

        $val = 'lamanmeister';
        $this->assertEquals(null, $profile->getProfile());
        $this->assertSame($profile, $profile->setProfile($val));
        $this->assertEquals($val, $profile->getProfile());

        $val = 64643;
        $this->assertEquals(null, $profile->getSize());
        $this->assertSame($profile, $profile->setSize($val));
        $this->assertEquals($val, $profile->getSize());

        $val = 'lamanmeister.xoo';
        $this->assertEquals(null, $profile->getName());
        $this->assertSame($profile, $profile->setName($val));
        $this->assertEquals($val, $profile->getName());

        $val = 'linkster';
        $this->assertEquals(null, $profile->getLink());
        $this->assertSame($profile, $profile->setLink($val));
        $this->assertEquals($val, $profile->getLink());

        $val = new DateTime('1978-01-02');
        $this->assertEquals(null, $profile->getDateUploaded());
        $this->assertSame($profile, $profile->setDateUploaded($val));
        $this->assertSame($val, $profile->getDateUploaded());
        */

    }



    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function setIdentifierShouldFailWithOriginalAsIdentifier()
    {
         $profile = new FileProfile();
         $profile->setIdentifier('original');
    }

    /**
     * @test
     */
    public function addPluginShouldAddPlugin()
    {
        $profile = new FileProfile();

        $mock1 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $mock2 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');

        $this->assertEquals(array(), $profile->getPlugins());

        $profile->addPlugin($mock1);

        $plugins = $profile->getPlugins();

        $this->assertCount(1, $plugins);

        $this->assertContains($mock1, $plugins);

        $this->assertSame($profile, $profile->addPlugin($mock2));

        $plugins = $profile->getPlugins();

        $this->assertCount(2, $plugins);

        $this->assertContains($mock1, $plugins);
        $this->assertContains($mock2, $plugins);
    }



    /**
     * @test
     */
    public function addFileVersionShouldAddFileVersion()
    {
        $profile = new FileProfile();

        $versionProvider = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');

        $this->assertSame($profile, $profile->addFileVersion('image', 'xooxer', $versionProvider));

    }


    /**
     * @test
     */
    public function fileVersionsShouldRegisterCorrectly()
    {
        $profile = $this->createProfileWithMockedVersions();

        $filelib = $this->createMockedFilelib();
        $profile->setFilelib($filelib);

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'image/lus'))
        ));
        $versionProviders = $profile->getFileVersions($file);
        $this->assertCount(2, $versionProviders);
        $this->assertContains('globalizer', $versionProviders);
        $this->assertContains('imagenizer', $versionProviders);

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'video/lus'))
        ));
        $versionProviders = $profile->getFileVersions($file);
        $this->assertCount(2, $versionProviders);
        $this->assertContains('globalizer', $versionProviders);
        $this->assertContains('videonizer', $versionProviders);

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'soo/soo'))
        ));
        $versionProviders = $profile->getFileVersions($file);
        $this->assertCount(0, $versionProviders);

    }

    public function provideFilesForHasVersionTest()
    {
        return array(
            array(true, 'globalizer', 'image/lus'),
            array(true, 'imagenizer', 'image/lus'),
            array(false, 'videonizer', 'image/lus'),
            array(false, 'videonizer', 'xoo/lus'),
            array(true, 'videonizer', 'video/lux'),
            array(false, 'globalizer', 'tussen/hof'),
            array(true, 'globalizer', 'video/avi'),
        );
    }

    /**
     * @test
     * @dataProvider provideFilesForHasVersionTest
     */
    public function fileHasVersionShouldWorkAsExpected($expected, $versionId, $mimetype)
    {
        $profile = $this->createProfileWithMockedVersions();
        $profile->setFilelib($this->createMockedFilelib());

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => $mimetype))
        ));

        $this->assertEquals($expected, $profile->fileHasVersion($file, $versionId));

    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getVersionProviderShouldFailWithNonExistingVersion()
    {
        $profile = $this->createProfileWithMockedVersions();
        $profile->setFilelib($this->createMockedFilelib());

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'xoo/lus'))
        ));

        $vp = $profile->getVersionProvider($file, 'globalizer');
    }

    /**
     * @test
     */
    public function getVersionProviderShouldReturnCorrectVersionProvider()
    {
        $profile = $this->createProfileWithMockedVersions();
        $profile->setFilelib($this->createMockedFilelib());

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'video/lus'))
        ));

        $vp = $profile->getVersionProvider($file, 'globalizer');

        $this->assertEquals('globalizer', $vp->getIdentifier());

    }



    /**
     * @return FileLibrary
     */
    private function createMockedFilelib()
    {
        $filelib = new FileLibrary();
        $fiop = $this->getMockBuilder('Xi\Filelib\File\FileOperator')->disableOriginalConstructor()->getMock();


        $fiop->expects($this->any())->method('getType')->will($this->returnCallBack(function ($file) {
            $split = explode('/', $file->getMimetype());
            return $split[0];
        }));

        $filelib->setFileOperator($fiop);

        return $filelib;
    }


    private function createProfileWithMockedVersions()
    {
        $profile = new FileProfile();

        $imageProvider = $this->getMock('Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $imageProvider->expects($this->any())->method('getIdentifier')->will($this->returnValue('imagenizer'));
        $imageProvider->expects($this->any())->method('getVersions')->will($this->returnValue(array('imagenizer')));
        $imageProvider->expects($this->any())->method('isSharedResourceAllowed')->will($this->returnValue(true));
        $imageProvider->expects($this->any())->method('providesFor')->will($this->returnCallback(function(File $file) { return $file->getMimetype() == 'image/png'; }));

        $videoProvider = $this->getMock('Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $videoProvider->expects($this->any())->method('getIdentifier')->will($this->returnValue('videonizer'));
        $videoProvider->expects($this->any())->method('getVersions')->will($this->returnValue(array('videonizer')));
        $videoProvider->expects($this->any())->method('isSharedResourceAllowed')->will($this->returnValue(false));
        $videoProvider->expects($this->any())->method('providesFor')->will($this->returnCallback(function(File $file) { return $file->getMimetype() == 'video/lus'; }));

        $globalProvider = $this->getMock('Xi\Filelib\Plugin\VersionProvider\VersionProvider');
        $globalProvider->expects($this->any())->method('getIdentifier')->will($this->returnValue('globalizer'));
        $globalProvider->expects($this->any())->method('getVersions')->will($this->returnValue(array('globalizer')));
        $globalProvider->expects($this->any())->method('isSharedResourceAllowed')->will($this->returnValue(true));
        $globalProvider->expects($this->any())->method('providesFor')->will($this->returnCallback(function(File $file) { return true; }));

        $profile->addFileVersion('image', 'imagenizer', $imageProvider);
        $profile->addFileVersion('video', 'videonizer', $videoProvider);

        $profile->addFileVersion('image', 'globalizer', $globalProvider);
        $profile->addFileVersion('video', 'globalizer', $globalProvider);

        $profile->addPlugin($imageProvider);
        $profile->addPlugin($videoProvider);
        $profile->addPlugin($globalProvider);

        return $profile;
    }

    /**
     * @return array
     */
    public function provideDataForisSharedResourceAllowed()
    {
        return array(
            array(true, 'image/png'),
            array(false, 'video/lus'),
            array(true, 'lussen/tussen'),
        );
    }

    /**
     * @test
     * @dataProvider provideDataForisSharedResourceAllowed
     */
    public function isSharedResourceAllowedShouldReturnCorrectResult($expected, $mimetype)
    {
        $profile = $this->createProfileWithMockedVersions();
        $profile->setFilelib($this->createMockedFilelib());

        $file = File::create(array('resource' => Resource::create(array('mimetype' => $mimetype))));

        $this->assertEquals($expected, $profile->isSharedResourceAllowed($file));
    }




}
