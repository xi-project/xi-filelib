<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tests\Plugin\Image;

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
     * @var FileOperator
     */
    private $fileOperator;

    public function setUp()
    {
        parent::setUp();

        $this->storage = $this->getMock('Xi\Filelib\Storage\Storage');

        $this->fileOperator = $this
            ->getMockBuilder('Xi\Filelib\File\FileOperator')
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new VersionPlugin(
            'xooxer',
            ROOT_TESTS . '/data/temp',
            'jpg',
            array()
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
                           ROOT_TESTS . '/data/temp',
                           'jpg'
                       ))
                       ->getMock();

        $filelib = $this->getMockedFilelib();
        $filelib->expects($this->any())->method('getStorage')->will($this->returnValue($this->storage));
        $plugin->setDependencies($filelib);

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
        $this->assertArrayHasKey('xi_filelib.profile.add', $events);
        $this->assertArrayHasKey('xi_filelib.file.after_upload', $events);
        $this->assertArrayHasKey('xi_filelib.file.delete', $events);
        $this->assertArrayHasKey('xi_filelib.resource.delete', $events);
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

    /**
     * @test
     */
    public function getsTempDir()
    {
        $this->assertEquals(ROOT_TESTS . '/data/temp', $this->plugin->getTempDir());
    }
}
