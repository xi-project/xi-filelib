<?php

namespace Xi\Filelib\Tests\File;

use Xi\Filelib\File\FileProfile;
use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\Event\PluginEvent;
use Xi\Filelib\Events;

/**
 * @group file-profile
 */
class FileProfileTest extends \Xi\Filelib\Tests\TestCase
{
    private $fileRepository;

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
        $this->assertArrayHasKey(Events::PLUGIN_AFTER_ADD, $events);
    }

    /**
     * @test
     */
    public function onPluginAddShouldAddPluginIfPluginHasProfile()
    {
        $plugin = $this->getMock('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->any())->method('hasProfile')->will($this->returnValue(true));

        $this->fileProfile->onPluginAdd(new PluginEvent($plugin));

        $this->assertContains($plugin, $this->fileProfile->getPlugins());
    }

    /**
     * @test
     */
    public function onPluginAddShouldNotAddPluginIfPluginDoesNotHaveProfile()
    {
        $plugin = $this->getMock('Xi\Filelib\Plugin\Plugin');
        $plugin->expects($this->any())->method('hasProfile')->will($this->returnValue(false));

        $this->fileProfile->onPluginAdd(new PluginEvent($plugin));

        $this->assertNotContains($plugin, $this->fileProfile->getPlugins());
    }

    /**
     * @test
     */
    public function addPluginShouldAddPlugin()
    {
        $plugin1 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');
        $plugin2 = $this->getMockForAbstractClass('Xi\Filelib\Plugin\Plugin');

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
    public function addFileVersionShouldAddFileVersion()
    {
        $versionProvider = $this->getMockForAbstractClass('Xi\Filelib\Plugin\VersionProvider\VersionProvider');

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
        $this->fileProfile->getVersionProvider($file, 'globalizer');
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

        $vp = $this->fileProfile->getVersionProvider($file, 'globalizer');

        $this->assertSame($versionProviders['global'], $vp);
    }

    private function addMockedVersionsToFileProfile()
    {
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
