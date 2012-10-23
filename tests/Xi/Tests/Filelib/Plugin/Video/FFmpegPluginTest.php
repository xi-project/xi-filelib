<?php

namespace Xi\Tests\Filelib\Plugin\Video;

use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\Video\FFmpegPlugin;

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
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Video\FFmpegPlugin'));
        $this->assertArrayHasKey(
            'Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider',
            class_parents('Xi\Filelib\Plugin\Video\FFmpegPlugin')
        );
    }

    /**
     * @test
     */
    public function getHelperShouldReturnFFmpegHelper()
    {
        $plugin = new FFmpegPlugin();
        $helper = $plugin->getHelper();

        $this->assertInstanceOf('Xi\Filelib\Plugin\Video\FFmpegHelper', $helper);

        $this->assertSame($helper, $plugin->getHelper());
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

    private function checkFFmpegFound()
    {
        return (boolean) trim(`sh -c "which ffmpeg"`);
    }

    private function getMockedStorage($path)
    {
        $storage = $this->getMock('Xi\Filelib\Storage\Storage');
        $storage->expects($this->once())->method('retrieve')
            ->with($this->isInstanceOf('Xi\Filelib\File\File'))
            ->will($this->returnValue(new FileObject($path)));
        return $storage;
    }
}
