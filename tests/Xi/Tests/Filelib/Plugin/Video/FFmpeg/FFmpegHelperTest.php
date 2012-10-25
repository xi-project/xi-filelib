<?php

namespace Xi\Tests\Filelib\Plugin\Video\FFmpeg;

use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\Exception\NotImplementedException;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileObject;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\Video\FFmpeg\FFmpegHelper;

class FFmpegHelperTest extends \Xi\Tests\Filelib\TestCase
{
    public function setUp()
    {
        if (!$this->checkFFmpegFound()) {
            $this->markTestSkipped('FFmpeg could not be found');
        }

        $this->testVideo = new FileObject(ROOT_TESTS . '/data/hauska-joonas.mp4');
        $this->ffmpeg = new FFmpegHelper();

        $this->config = array(
            'command' => 'ffmpeg',
            'options' => array(
                'y' => true
            ),
            'inputs' => array(
                'foo' => array(
                    'filename' => true,
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
    }

    public function tearDown()
    {
        unset($this->ffmpeg);
    }

    /**
     * @test
     */
    public function classShouldExist()
    {
        $this->assertTrue(class_exists('Xi\Filelib\Plugin\Video\FFmpeg\FFmpegHelper'));
    }

    /**
     * @test
     * @expectedException InvalidArgumentException
     */
    public function constructorShouldFailWithNonArrayOptions()
    {
        $helper = new FFmpegHelper('this is not an array');
    }

    /**
     * @test
     */
    public function constructorShouldPassWithArrayOptions()
    {
        $options = array('lussen' => 'hofer', 'tussen' => 'lussen');
        $helper = new FFmpegHelper($options);
    }

    /**
     * @test
     */
    public function settersAndGettersShouldWorkAsExpected()
    {
        $ffmpeg = new FFmpegHelper();

        $options = array(
            'foo' => array(
                'filename' => 'foo.png',
                'options' => array(
                    'vframes' => '1'
                )
            )
        );

        $this->assertEquals(array(), $ffmpeg->getOptions());
        $ffmpeg->setOptions($options);
        $this->assertEquals($options, $ffmpeg->getOptions());

        $this->assertEquals(array(), $ffmpeg->getInputs());
        $ffmpeg->setInputs($options);
        $this->assertEquals($options, $ffmpeg->getInputs());

        $this->assertEquals(array(), $ffmpeg->getOutputs());
        $ffmpeg->setOutputs($options);
        $this->assertEquals($options, $ffmpeg->getOutputs());
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
        $ffmpeg = new FFmpegHelper($options);
    }

    public function numberedOutputFilenames()
    {
        return array(
            array('still_%d.png'),
            array('still_%03d.png')
        );
    }

    /**
     * @test
     * @dataProvider numberedOutputFilenames
     * @expectedException Xi\Filelib\Exception\NotImplementedException
     */
    public function setOutputsShouldThrowExceptionForNumberedFilenames($filename)
    {
        $options = array(
            'outputs' => array(
                'foo' => array('filename' => $filename)
            )
        );
        $ffmpeg = new FFmpegHelper($options);
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
            FFmpegHelper::shellArguments($options)
        );
    }

    public function testGetCommandLine()
    {
        $ffmpeg = new FFmpegHelper($this->config);
        $tmpDir = realpath(sys_get_temp_dir());

        $this->assertEquals(
            "ffmpeg -y -r '1' -i 'video.mp4' -r '20' -i 'simpsons.mp4' -vframes '1' '$tmpDir/still.png' -s '1280x720' '$tmpDir/video.webm'",
            $ffmpeg->getCommandLine('video.mp4', $tmpDir)
        );
    }

    /**
     * @test
     */
    public function testgetOutputPathnames()
    {
        $ffmpeg = new FFmpegHelper($this->config);
        $tmpDir = realpath(sys_get_temp_dir());

        $this->assertEquals(
            array(
                'stills' => "$tmpDir/still.png",
                'webm' => "$tmpDir/video.webm"
            ),
            $ffmpeg->getOutputPathnames($tmpDir)
        );
    }

    /**
     * @test
     */
    public function testGetVideoInfo()
    {
        $filename = $this->testVideo->getPathname();
        $expected = <<<JSON
{
    "format": {
        "filename": "$filename",
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

        $this->assertEquals(
            json_decode($expected)->format,
            $this->ffmpeg->getVideoInfo($this->testVideo)->format
        );
    }

    /**
     * @test
     */
    public function testGetDuration()
    {
        $this->assertEquals(3.989000, $this->ffmpeg->getDuration($this->testVideo));
    }

    private function checkFFmpegFound()
    {
        return (boolean) trim(`sh -c "which ffmpeg"`);
    }
}
