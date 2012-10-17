<?php

namespace Xi\Tests\Filelib\Plugin\Video;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\Video\FFmpegPlugin;

class FFmpegPluginTest extends \Xi\Tests\Filelib\TestCase
{
    public function setUp()
    {
        if (!$this->checkFFmpegFound()) {
            $this->markTestSkipped('FFmpeg could not be found');
        }
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

    private function checkFFmpegFound()
    {
        $found = trim(`ffmpeg -version &>/dev/null && echo "true" || echo "false"`);
        return ('true' === $found);
    }
}