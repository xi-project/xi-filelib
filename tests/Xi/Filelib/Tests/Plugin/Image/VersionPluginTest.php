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
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\Resource;
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

    /**
     * @var FileOperator
     */
    private $fileOperator;

    public function setUp()
    {
        parent::setUp();

        $this->storage = $this->getMock('Xi\Filelib\Storage\Storage');

        $this->fileOperator = $this->getMockedFileOperator(array('default'));

        $this->plugin = new VersionPlugin(
            'xooxer',
            array(),
            'jpg'
        );
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
            $this->getMockedStorage(),
            $this->getMockedPlatform()
        );
        $filelib->addPlugin($this->plugin);

        $this->assertFalse(
            $this->plugin->providesFor(
                File::create(array('resource' => Resource::create(array('mimetype' => 'video/avi'))))
            )
        );

        $this->assertTrue(
            $this->plugin->providesFor(
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
    public function createVersionsShouldCreateVersions()
    {
        $retrievedPath = ROOT_TESTS . '/data/illusive-manatee.jpg';

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
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

        $filelib = $this->getMockedFilelib(null, $this->fileOperator);
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
         $this->assertEquals(array('xooxer'), $this->plugin->getVersions());
    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWork()
    {
        $extension = 'lus';
        $this->assertSame('jpg', $this->plugin->getExtension());
    }

    /**
     * @test
     */
    public function getExtensionForShouldDelegateToGetExtension()
    {
        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\Image\VersionPlugin')
                       ->setMethods(array('getExtension'))
                       ->disableOriginalConstructor()
                       ->getMock();

        $plugin->expects($this->once())->method('getExtension')->will($this->returnValue('lus'));

        $ret = $plugin->getExtensionFor($this->getMockedFile(), 'xooxoo');

        $this->assertSame('lus', $ret);
    }

    /**
     * @test
     */
    public function getExtensionForShouldDelegateToParentToAutodetectExtension()
    {
        $storage = $this->getMockedStorage();
        $filelib = new FileLibrary(
            $storage,
            $this->getMockedPlatform()
        );

        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\Image\VersionPlugin')
            ->setMethods(array('getExtension'))
            ->disableOriginalConstructor()
            ->getMock();
        $plugin->attachTo($filelib);

        $plugin->expects($this->once())->method('getExtension')->will($this->returnValue(null));

        $resource = $this->getMockedResource();
        $file = File::create(array('resource' => $resource));

        $storage->expects($this->once())
            ->method('retrieveVersion')
            ->with($resource, 'xooxoo', null)
            ->will($this->returnValue(ROOT_TESTS . '/data/self-lussing-manatee.jpg'));

        $ret = $plugin->getExtensionFor($file, 'xooxoo');

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
