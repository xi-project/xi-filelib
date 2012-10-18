<?php

namespace Xi\Tests\Filelib\Plugin\Video;

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
    public function settersAndGettersShouldWorkAsExpected()
    {
        $this->plugin = new FFmpegPlugin();
        $this->assertTrue($this->checkFFmpegFound());
    }

    /**
     * @test
     */
    public function testGetVideoInfo()
    {
        $path = ROOT_TESTS . '/data/hauska-joonas.mp4';
        $filelib = $this->getFilelib()->setStorage($this->getMockedStorage($path));
        $this->plugin->setFilelib($filelib);

        $expected = <<<JSON
{
    "format": {
        "filename": "$path",
        "nb_streams": 2,
        "format_name": "mov,mp4,m4a,3gp,3g2,mj2",
        "format_long_name": "QuickTime / MOV",
        "start_time": "0.000000",
        "duration": "3.989000",
        "size": "6852578",
        "bit_rate": "13742949",
        "tags": {
            "major_brand": "isom",
            "minor_version": "0",
            "compatible_brands": "isom3gp4",
            "creation_time": "2012-05-22 06:16:16"
        }
    }
}
JSON;

        $video = File::create(array('id' => 1, 'name' => basename($path)));
        $this->assertEquals(json_decode($expected), $this->plugin->getVideoInfo($video));
    }

    /**
     * @test
     */
    public function testGetDuration()
    {
        $path = ROOT_TESTS . '/data/hauska-joonas.mp4';
        $filelib = $this->getFilelib()->setStorage($this->getMockedStorage($path));
        $this->plugin->setFilelib($filelib);

        $video = File::create(array('id' => 1, 'name' => basename($path)));
        $this->assertEquals(3.989000, $this->plugin->getDuration($video));
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