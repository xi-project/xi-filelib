<?php

namespace Xi\Filelib\Tests\Profile;

use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Profile\FileProfile;
use Xi\Filelib\File\File;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Event\PluginEvent;
use Xi\Filelib\Events;

class FileProfileTest extends \Xi\Filelib\Tests\TestCase
{
    /**
     * @var FileProfile
     */
    private $fileProfile;

    protected function setUp()
    {
        $this->fileProfile = new FileProfile('lussen');
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Profile\FileProfile'));
        $this->assertContains(
            'Symfony\Component\EventDispatcher\EventSubscriberInterface', class_implements('Xi\Filelib\Profile\FileProfile')
        );
    }

    /**
     * @test
     */
    public function classShouldSubscribeToCorrectEvents()
    {
        $events = FileProfile::getSubscribedEvents();
        $this->assertArrayHasKey(Events::PLUGIN_AFTER_ADD, $events);
    }

    /**
     * @test
     */
    public function onPluginAddShouldAddPluginIfPluginHasProfile()
    {
        $plugin = $this->getMock('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->any())->method('belongsToProfile')->will($this->returnValue(true));


        $this->fileProfile->onPluginAdd(new PluginEvent($plugin, $this->getMockedFilelib()));

        $this->assertContains($plugin, $this->fileProfile->getPlugins());
    }

    /**
     * @test
     */
    public function onPluginAddShouldNotAddPluginIfPluginDoesNotHaveProfile()
    {
        $plugin = $this->getMock('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->any())->method('belongsToProfile')->will($this->returnValue(false));

        $this->fileProfile->onPluginAdd(new PluginEvent($plugin, $this->getMockedFilelib()));

        $this->assertNotContains($plugin, $this->fileProfile->getPlugins());
    }

    /**
     * @test
     */
    public function addPluginShouldAddPlugin()
    {
        $plugin1 = $this->getMockedPlugin();
        $plugin2 = $this->getMockedPlugin();

        $this->assertEquals(array(), $this->fileProfile->getPlugins());

        $this->fileProfile->addPlugin($plugin1);

        $plugins = $this->fileProfile->getPlugins();

        $this->assertCount(1, $plugins);
        $this->assertContains($plugin1, $plugins);

        $this->assertSame($this->fileProfile, $this->fileProfile->addPlugin($plugin2));

        $plugins = $this->fileProfile->getPlugins();

        $this->assertCount(2, $plugins);

        $this->assertContains($plugin1, $plugins);
        $this->assertContains($plugin2, $plugins);
    }

    /**
     * @test
     */
    public function addingVersionProviderAddsVersions()
    {
        $file = File::create();

        $plugin1 = $this->getMockedVersionProvider(array('tenhunen', 'imaisee'));
        $plugin1
            ->expects($this->atLeastOnce())
            ->method('isApplicableTo')
            ->with($file)
            ->will($this->returnValue(true));

        $this->fileProfile->addPlugin($plugin1);

        $this->assertSame($plugin1, $this->fileProfile->getVersionProvider($file, Version::get('tenhunen')));
        $this->assertSame($plugin1, $this->fileProfile->getVersionProvider($file, Version::get('imaisee')));
    }


    /**
     * @test
     */
    public function addFileVersionShouldAddFileVersion()
    {
        $versionProvider = $this->getMockedVersionProvider();

        $this->assertSame(
            $this->fileProfile,
            $this->fileProfile->addFileVersion('xooxer', $versionProvider)
        );
    }

    /**
     * @test
     */
    public function fileVersionsShouldRegisterCorrectly()
    {
        $this->addMockedVersionsToFileProfile();

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'image/png'))
        ));
        $versionProviders = $this->fileProfile->getFileVersions($file);

        $this->assertCount(2, $versionProviders);
        $this->assertContains('globalizer', $versionProviders);
        $this->assertContains('imagenizer', $versionProviders);

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'video/lus'))
        ));
        $versionProviders = $this->fileProfile->getFileVersions($file);
        $this->assertCount(2, $versionProviders);
        $this->assertContains('globalizer', $versionProviders);
        $this->assertContains('videonizer', $versionProviders);

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'soo/soo'))
        ));
        $versionProviders = $this->fileProfile->getFileVersions($file);
        $this->assertCount(1, $versionProviders);
    }

    public function provideFilesForHasVersionTest()
    {
        return array(
            array(true, 'globalizer', 'image/lus'),
            array(false, 'imagenizer', 'image/lus'),
            array(true, 'imagenizer', 'image/png'),
            array(false, 'videonizer', 'image/lus'),
            array(false, 'videonizer', 'xoo/lus'),
            array(false, 'videonizer', 'video/lux'),
            array(true, 'globalizer', 'tussen/hof'),
            array(true, 'globalizer', 'video/avi'),
        );
    }

    /**
     * @test
     * @dataProvider provideFilesForHasVersionTest
     */
    public function fileHasVersionShouldWorkAsExpected($expected, $versionId, $mimetype)
    {
        $versionId = Version::get($versionId);

        $this->addMockedVersionsToFileProfile();

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => $mimetype))
        ));

        $this->assertEquals($expected, $this->fileProfile->fileHasVersion($file, $versionId));
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function getVersionProviderShouldFailWithNonExistingVersion()
    {
        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'xoo/lus'))
        ));
        $this->fileProfile->getVersionProvider($file, Version::get('globalizer'));
    }

    /**
     * @test
     */
    public function getVersionProviderShouldReturnCorrectVersionProvider()
    {
        $versionProviders = $this->addMockedVersionsToFileProfile();

        $file = File::create(array(
            'resource' => Resource::create(array('mimetype' => 'video/lus'))
        ));

        $vp = $this->fileProfile->getVersionProvider($file, Version::get('globalizer'));

        $this->assertSame($versionProviders['global'], $vp);
    }

    private function addMockedVersionsToFileProfile()
    {
        $imageProvider = $this->getMockedVersionProvider(array('imagenizer'));
        $imageProvider->expects($this->any())->method('isSharedResourceAllowed')->will($this->returnValue(true));
        $imageProvider->expects($this->any())->method('isApplicableTo')->will($this->returnCallback(function(File $file) { return $file->getMimetype() == 'image/png'; }));

        $videoProvider = $this->getMockedVersionProvider(array('videonizer'));
        $videoProvider->expects($this->any())->method('isSharedResourceAllowed')->will($this->returnValue(false));
        $videoProvider->expects($this->any())->method('isApplicableTo')->will($this->returnCallback(function(File $file) { return $file->getMimetype() == 'video/lus'; }));

        $globalProvider = $this->getMockedVersionProvider(array('globalizer'));
        $globalProvider->expects($this->any())->method('isSharedResourceAllowed')->will($this->returnValue(true));
        $globalProvider->expects($this->any())->method('isApplicableTo')->will($this->returnCallback(function(File $file) { return true; }));

        $this->fileProfile->addFileVersion('imagenizer', $imageProvider);
        $this->fileProfile->addFileVersion('videonizer', $videoProvider);
        $this->fileProfile->addFileVersion('globalizer', $globalProvider);

        $this->fileProfile->addPlugin($imageProvider);
        $this->fileProfile->addPlugin($videoProvider);
        $this->fileProfile->addPlugin($globalProvider);

        return array(
            'video' => $videoProvider,
            'global' => $globalProvider,
            'image' => $imageProvider
        );

    }

    /**
     * @return array
     */
    public function provideDataForIsSharedResourceAllowed()
    {
        return array(
            array(true, 'image/png'),
            array(false, 'video/lus'),
            array(true, 'lussen/tussen'),
        );
    }

    /**
     * @test
     * @dataProvider provideDataForIsSharedResourceAllowed
     */
    public function isSharedResourceAllowedShouldReturnCorrectResult($expected, $mimetype)
    {
        $this->addMockedVersionsToFileProfile();

        $file = File::create(array('resource' => Resource::create(array('mimetype' => $mimetype))));

        $this->assertEquals($expected, $this->fileProfile->isSharedResourceAllowed($file));
    }
}
