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
    public function settersAndGettersShouldWorkAsExpected()
    {
        $plugin = new FFmpegPlugin();

        $options = array(
            'foo' => array(
                'filename' => 'foo.png',
                'options' => array(
                    'vframes' => '1'
                )
            )
        );

        $this->assertEquals(array(), $plugin->getGlobalOptions());
        $plugin->setGlobalOptions($options);
        $this->assertEquals($options, $plugin->getGlobalOptions());

        $this->assertEquals(array(), $plugin->getInputs());
        $plugin->setInputs($options);
        $this->assertEquals($options, $plugin->getInputs());

        $this->assertEquals(array(), $plugin->getOutputs());
        $plugin->setOutputs($options);
        $this->assertEquals($options, $plugin->getOutputs());
    }

    public function invalidOutputFilenames()
    {
        return array(
            array(''),
            array('/foo'),
            array('/foo.ext'),
            array('/foo/bar.ext'),
            array('../foo.ext'),
            array('../bar/./foo.ext')
        );
    }

    /**
     * @test
     * @dataProvider invalidOutputFilenames
     * @expectedException Xi\Filelib\Exception\InvalidArgumentException
     */
    public function setOutputsShouldThrowExceptionForInvalidFilenames($filename)
    {
        $options = array(
            'outputs' => array(
                'foo' => array('filename' => $filename)
            )
        );
        $plugin = new FFmpegPlugin($options);
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
    public function testShellArguments()
    {
        $options = array(
            'ss' => '00:00:01.000',
            'r' => '1/25',
            's' => '1920x1080',
            'aspect' => '16:9',
            'f' => 'image2',
            'vframes' => '3'
        );
        $this->assertEquals(
            "-ss '00:00:01.000' -r '1/25' -s '1920x1080' -aspect '16:9' -f 'image2' -vframes '3'",
            FFmpegPlugin::shellArguments($options)
        );
    }

    public function testGetCommand()
    {
        $config = array(
            'globalOptions' => array(
                'y' => true
            ),
            'inputs' => array(
                'foo' => array(
                    'filename' => 'video.mp4',
                    'options' => array('r' => '1')
                ),
                'bar' => array(
                    'filename' => 'simpsons.mp4',
                    'options' => array('r' => '20')
                )
            ),
            'outputs' => array(
                'stills' => array(
                    'filename' => 'still.png',
                    'options' => array(
                        'vframes' => '1'
                    )
                ),
                'webm' => array(
                    'filename' => 'video.webm',
                    'options' => array(
                        's' => '1280x720'
                    )
                )
            )
        );
        $plugin = new FFmpegPlugin($config);

        $file = $this->testVideo;
        $this->assertEquals(
            "ffmpeg -y -r '1' -i 'video.mp4' -r '20' -i 'simpsons.mp4' -vframes '1' 'still.png' -s '1280x720' 'video.webm'",
            $plugin->getCommand()
        );
    }

    /**
     * @test
     */
    public function testGetVideoInfo()
    {
        $filelib = $this->getFilelib()->setStorage($this->getMockedStorage($this->testVideo));
        $this->plugin->setFilelib($filelib);

        $expected = <<<JSON
{
    "format": {
        "filename": "$this->testVideo",
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

        $video = File::create(array('id' => 1, 'name' => basename($this->testVideo)));
        $this->assertEquals(json_decode($expected)->format, $this->plugin->getVideoInfo($video)->format);
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
