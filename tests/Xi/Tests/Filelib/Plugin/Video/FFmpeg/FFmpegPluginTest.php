<?php

namespace Xi\Tests\Filelib\Plugin\Video\FFmpeg;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\Plugin\Video\FFmpeg\FFmpegPlugin;

/**
 * @group plugin
 * @group ffmpeg
 */
class FFmpegPluginTest extends \Xi\Tests\Filelib\TestCase
{
    /**
     * @var FFmpegPlugin
     */
    private $plugin;

    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $storage;

    /**
     * @var string
     */
    private $testVideo;

    public function setUp()
    {
        if (!$this->checkFFmpegFound()) {
            $this->markTestSkipped('FFmpeg could not be found');
        }

        $this->testVideo = ROOT_TESTS . '/data/hauska-joonas.mp4';

        $this->storage = $this->getMockBuilder('Xi\Filelib\Storage\Storage')
            ->disableOriginalConstructor()
            ->getMock();

        $options = array(
            'command' => 'echo',
            'options' => array(
                'y' => true
            ),
            'inputs' => array(
                'original' => array(
                    'filename' => true,
                    'options' => array(
                        'ss' => '00:00:01.000',
                        'r' => '1',
                        'vframes' => '1'
                    )
                )
            ),
            'outputs' => array(
                'still' => array(
                    'filename' => 'still.png',
                    'options' => array()
                ),
                'video' => array(
                    'filename' => 'video.webm',
                    'options' => array()
                ),
            )
        );

        $this->tempDir = ROOT_TESTS . '/data/temp';

        $this->plugin = new FFmpegPlugin(
            $this->storage,
            $this->getMock('Xi\Filelib\Publisher\Publisher'),
            $this->getMockBuilder('Xi\Filelib\File\FileOperator')
                ->disableOriginalConstructor()
                ->getMock(),
            $this->tempDir,
            $options
        );
    }

    public function tearDown()
    {
        unset($this->plugin);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Video\FFmpeg\FFmpegPlugin'));
        $this->assertArrayHasKey(
            'Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider',
            class_parents('Xi\Filelib\Plugin\Video\FFmpeg\FFmpegPlugin')
        );
    }

    /**
     * @test
     */
    public function getHelperShouldReturnFFmpegHelper()
    {
        $helper = $this->plugin->getHelper();

        $this->assertInstanceOf('Xi\Filelib\Plugin\Video\FFmpeg\FFmpegHelper', $helper);
        $this->assertSame($helper, $this->plugin->getHelper());
    }

    /**
     * @test
     */
    public function testCreateVersions()
    {
        $file = File::create(array('id' => 1, 'resource' => Resource::create()));

        $fobject = $this->getMockBuilder('Xi\Filelib\File\FileObject')
                        ->setConstructorArgs(array($this->testVideo))
                        ->getMock();
        $fobject->expects($this->once())->method('getPathName')->will($this->returnValue($this->testVideo));

        $this->storage
            ->expects($this->once())
            ->method('retrieve')
            ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
            ->will($this->returnValue($fobject));

        $this->assertEquals(
            array(
                'still' => "$this->tempDir/still.png",
                'video' => "$this->tempDir/video.webm"
            ),
            $this->plugin->createVersions($file)
        );
    }

     /**
     * @test
     */
    public function testExtensionFor()
    {
        $this->assertEquals('png', $this->plugin->getExtensionFor('still'));
        $this->assertEquals('webm', $this->plugin->getExtensionFor('video'));
    }

    /**
     * @test
     */
    public function testGetVersions()
    {
        $this->assertEquals(array('still', 'video'), $this->plugin->getVersions());
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

    private function checkFFmpegFound()
    {
        // Skip 4 now because no version identifier.
        return false;
        return (boolean) trim(`sh -c "which ffmpeg"`);
    }
}
