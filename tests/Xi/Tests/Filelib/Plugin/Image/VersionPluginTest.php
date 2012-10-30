<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Tests\Filelib\Plugin\Image;

use Imagick;
use Xi\Filelib\Plugin\Image\VersionPlugin;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Storage\Storage;
use Xi\Filelib\Publisher\Publisher;
use Xi\Filelib\File\Resource;

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
     * @var Publisher
     */
    private $publisher;

    /**
     * @var FileOperator
     */
    private $fileOperator;

    public function setUp()
    {
        parent::setUp();

        $this->storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $this->publisher = $this->getMock('Xi\Filelib\Publisher\Publisher');
        $this->fileOperator = $this->getMock('Xi\Filelib\File\FileOperator');

        $this->plugin = new VersionPlugin(
            $this->storage,
            $this->publisher,
            $this->fileOperator,
            ROOT_TESTS . '/data/temp'
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
        $this->assertEquals(array('image'), $this->plugin->getProvidesFor());
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

        $fobject = $this->getMockBuilder('Xi\Filelib\File\FileObject')
                        ->setConstructorArgs(array(ROOT_TESTS . '/data/self-lussing-manatee.jpg'))
                        ->getMock();
        $fobject->expects($this->once())->method('getPathName')->will($this->returnValue($retrievedPath));

        $this->storage->expects($this->once())->method('retrieve')->with($this->isInstanceOf('Xi\Filelib\File\Resource'))->will($this->returnValue($fobject));

        $helper = $this->getMock('Xi\Filelib\Plugin\Image\ImageMagickHelper');

        $mock = $this->getMock('Imagick');
        $mock->expects($this->once())
             ->method('writeImage')
             ->with($this->matchesRegularExpression('#^' . ROOT_TESTS . '/data/temp#'));

        $helper->expects($this->once())->method('createImagick')->with($this->equalTo($retrievedPath))->will($this->returnValue($mock));
        $helper->expects($this->once())->method('execute')->with($this->equalTo($mock));

        $plugin = $this->getMockBuilder('Xi\Filelib\Plugin\Image\VersionPlugin')
                       ->setMethods(array('getImageMagickHelper'))
                       ->setConstructorArgs(array(
                           $this->storage,
                           $this->publisher,
                           $this->fileOperator,
                           ROOT_TESTS . '/data/temp'
                       ))
                       ->getMock();

        $plugin->expects($this->any())->method('getImageMagickHelper')->will($this->returnValue($helper));

        $ret = $plugin->createVersions($file);

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
        $this->assertArrayHasKey('fileprofile.add', $events);
        $this->assertArrayHasKey('file.afterUpload', $events);
        $this->assertArrayHasKey('file.publish', $events);
        $this->assertArrayHasKey('file.unpublish', $events);
        $this->assertArrayHasKey('file.delete', $events);
        $this->assertArrayHasKey('resource.delete', $events);
    }

    /**
     * @test
     */
    public function getVersionsShouldReturnArrayOfOneContainingIdentifier()
    {
         $this->plugin->setIdentifier('xooxer');

         $this->assertEquals(array('xooxer'), $this->plugin->getVersions());
    }

    /**
     * @test
     */
    public function gettersAndSettersShouldWork()
    {
        $extension = 'lus';

        $this->assertNull($this->plugin->getExtension());
        $this->assertSame($this->plugin, $this->plugin->setExtension($extension));
        $this->assertEquals($extension, $this->plugin->getExtension());
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

        $plugin->expects($this->once())->method('getExtension');

        $plugin->getExtensionFor('xooxoo');
    }
}
