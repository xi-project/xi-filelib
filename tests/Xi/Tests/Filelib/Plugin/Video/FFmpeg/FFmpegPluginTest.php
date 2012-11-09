<?php

namespace Xi\Tests\Filelib\Plugin\Video\FFmpeg;

use Xi\Filelib\File\File;
use Xi\Filelib\File\Resource;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\Video\FFmpeg\FFmpegPlugin;

class FFmpegPluginTest extends \Xi\Tests\Filelib\TestCase
{
    public function setUp()
    {
        if (!$this->checkFFmpegFound()) {
            $this->markTestSkipped('FFmpeg could not be found');
        }

        $this->testVideo = ROOT_TESTS . '/data/hauska-joonas.mp4';
        $this->plugin = new FFmpegPlugin();
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
        $plugin = new FFmpegPlugin();
        $helper = $plugin->getHelper();

        $this->assertInstanceOf('Xi\Filelib\Plugin\Video\FFmpeg\FFmpegHelper', $helper);

        $this->assertSame($helper, $plugin->getHelper());
    }

    /**
     * @test
     */
    public function testCreateVersions()
    {
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
                )
            )
        );

        $file = File::create(array('id' => 1, 'resource' => Resource::create()));


        $fobject = $this->getMockBuilder('Xi\Filelib\File\FileObject')
                        ->setConstructorArgs(array($this->testVideo))
                        ->getMock();
        $fobject->expects($this->once())->method('getPathName')->will($this->returnValue($this->testVideo));

        $storage = $this->getMockForAbstractClass('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())
            ->method('retrieve')->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
            ->will($this->returnValue($fobject));

        $tmpDir = realpath(sys_get_temp_dir());

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $filelib->expects($this->any())->method('getTempDir')->will($this->returnValue($tmpDir));
        $filelib->expects($this->any())->method('getStorage')->will($this->returnValue($storage));

        $ffmpeg = new FFmpegPlugin($options);
        $ffmpeg->setFilelib($filelib);

        $this->assertEquals(
            array('still' => "$tmpDir/still.png"),
            $ffmpeg->createVersions($file)
        );
    }

     /**
     * @test
     */
    public function testExtensionFor()
    {
        $options = array(
            'outputs' => array(
                'foo' => array(
                    'filename' => 'still.png'
                ),
                'bar' => array(
                    'filename' => 'video.webm'
                )
            )
        );
        $plugin = new FFmpegPlugin($options);

        $this->assertEquals('png', $plugin->getExtensionFor('foo'));
        $this->assertEquals('webm', $plugin->getExtensionFor('bar'));
    }

    /**
     * @test
     */
    public function testGetVersions()
    {
        $options = array(
            'outputs' => array(
                'alpha' => array('filename' => 'lus.png'),
                'beta' => array('filename' => 'tus.png')
            )
        );
        $plugin = new FFmpegPlugin($options);

        $this->assertEquals(array('alpha', 'beta'), $plugin->getVersions());
    }

    /**
     * @test
     */
    public function pluginShouldAllowSharedResource()
    {
        $plugin = new FFmpegPlugin();
        $this->assertTrue($plugin->isSharedResourceAllowed());
    }

    /**
     * @test
     */
    public function pluginShouldAllowSharedVersions()
    {
        $plugin = new FFmpegPlugin();
        $this->assertTrue($plugin->areSharedVersionsAllowed());
    }

    private function checkFFmpegFound()
    {
        return (boolean) trim(`sh -c "which ffmpeg"`);
    }

    private function getMockedStorage($path)
    {
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieve')
            ->with($this->isInstanceOf('Xi\Filelib\File\Resource'))
            ->will($this->returnValue(new FileObject($path)));
        return $storage;
    }
}
