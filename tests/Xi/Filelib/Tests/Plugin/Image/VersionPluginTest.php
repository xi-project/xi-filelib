<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

use Imagick;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileRepository;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Events;

/**
 * @group plugin
 */
class VersionPluginTest extends TestCase
{
    /**
     * @var VersionPlugin
     */
    private $plugin;

    /**
     * @var Storage
     */
    private $storage;

    public function setUp()
    {
        parent::setUp();

        $this->storage = $this->getMockedStorage();

        $this->plugin = new VersionPlugin(
            'xooxer',
            array(),
            'jpg'
        );
    }

    /**
     * @test
     */
    public function canBeLazy()
    {
        $this->assertTrue($this->plugin->canBeLazy());
    }

    /**
     * @test
     */
    public function classExtendsAbstractPlugin()
    {
        $this->assertArrayHasKey(
            'Xi\Filelib\Plugin\AbstractPlugin',
            class_parents($this->plugin)
        );
    }

    /**
     * @test
     */
    public function pluginShouldProvideForImage()
    {
        $filelib = new FileLibrary(
            $this->getMockedStorageAdapter(),
            $this->getMockedBackendAdapter()
        );
        $filelib->addPlugin($this->plugin);

        $this->assertFalse(
            $this->plugin->isApplicableTo(
                File::create(array('resource' => Resource::create(array('mimetype' => 'video/avi'))))
            )
        );

        $this->assertTrue(
            $this->plugin->isApplicableTo(
                File::create(array('resource' => Resource::create(array('mimetype' => 'image/png'))))
            )
        );
    }

    /**
     * @test
     */
    public function pluginShouldAllowSharedResource()
    {
        $this->assertTrue($this->plugin->isSharedResourceAllowed());
    }

    /**
     * @test
     */
    public function pluginShouldAllowSharedVersions()
    {
        $this->assertTrue($this->plugin->areSharedVersionsAllowed());
    }

    /**
     * @test
     */
    public function getImageMagickHelperShouldReturnImageMagickHelper()
    {
        $helper = $this->plugin->getImageMagickHelper();
        $this->assertInstanceOf('Xi\Filelib\Plugin\Image\ImageMagickHelper', $helper);
        $this->assertSame($helper, $this->plugin->getImageMagickHelper());
    }

    /**
     * @test
     */
    public function createProvidedVersionsShouldCreateVersions()
    {
        $retrievedPath = ROOT_TESTS . '/data/illusive-manatee.jpg';

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->with($this->isInstanceOf('Xi\Filelib\Resource\Resource'))
            ->will($this->returnValue($retrievedPath));

        $helper = $this->getMockBuilder('Xi\Filelib\Plugin\Image\ImageMagickHelper')->disableOriginalConstructor()->getMock();

        $mock = $this->getMock('Imagick');
        $mock->expects($this->once())
             ->method('writeImage')
             ->with($this->matchesRegularExpression('#^' . ROOT_TESTS . '/data/temp#'));

        $helper->expects($this->once())->method('createImagick')->with($this->equalTo($retrievedPath))->will($this->returnValue($mock));
        $helper->expects($this->once())->method('execute')->with($this->equalTo($mock));

        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\Image\VersionPlugin')
                       ->setMethods(array('getImageMagickHelper'))
                       ->setConstructorArgs(array(
                           'tussi',
                           array(),
                           'jpg'
                       ))
                       ->getMock();

        $pm = $this->getMockedProfileManager(array('xooxer'));
        $filelib = $this->getMockedFilelib(null, null, null, null, null, null, null, null, $pm);
        $filelib->expects($this->any())->method('getStorage')->will($this->returnValue($this->storage));
        $filelib->expects($this->any())->method('getTempDir')->will($this->returnValue(ROOT_TESTS . '/data/temp'));
        $plugin->attachTo($filelib);

        $plugin->expects($this->any())->method('getImageMagickHelper')->will($this->returnValue($helper));

        $ret = $plugin->createTemporaryVersions($file);

        $this->assertInternalType('array', $ret);

        foreach ($ret as $version => $tmp) {
            $this->assertRegExp('#^' . ROOT_TESTS . '/data/temp#', $tmp);
        }
    }

    /**
     * @test
     */
    public function getSubscribedEventsShouldReturnCorrectEvents()
    {
        $events = VersionPlugin::getSubscribedEvents();
        $this->assertArrayHasKey(Events::PROFILE_AFTER_ADD, $events);
        $this->assertArrayHasKey(Events::FILE_AFTER_AFTERUPLOAD, $events);
        $this->assertArrayHasKey(Events::FILE_AFTER_DELETE, $events);
        $this->assertArrayHasKey(Events::RESOURCE_AFTER_DELETE, $events);
    }

    /**
     * @test
     */
    public function getVersionsShouldReturnArrayOfOneContainingIdentifier()
    {
         $this->assertEquals(array('xooxer'), $this->plugin->getProvidedVersions());
    }

    /**
     * @test
     */
    public function getExtensionShouldUsePreDefinedMimeType()
    {
        $plugin = new VersionPlugin('xooxoo', array(), 'application/rpki-ghostbusters');
        $ret = $plugin->getExtension($this->getMockedFile(), 'xooxoo');
        $this->assertSame('gbr', $ret);
    }

    /**
     * @test
     */
    public function getExtensionShouldDelegateToParentToAutodetectExtension()
    {
        $storage = $this->getMockedStorage();

        $filelib = $this->getMockedFilelib(
            null,
            array(
                'storage' => $storage
            )
        );

        $plugin = new VersionPlugin('xooxoo', array(), null);
        $plugin->attachTo($filelib);

        $resource = $this->getMockedResource();
        $file = File::create(array('resource' => $resource));

        $storage->expects($this->once())
            ->method('retrieveVersion')
            ->with($resource, 'xooxoo')
            ->will($this->returnValue(ROOT_TESTS . '/data/self-lussing-manatee.jpg'));

        $ret = $plugin->getExtension($file, 'xooxoo');

        $this->assertSame('jpg', $ret);
    }



    /**
     * @test
     */
    public function injectsTempDirFromFilelib()
    {
        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->any())->method('getTempDir')->will($this->returnValue('lussutushovi'));
    }
}
