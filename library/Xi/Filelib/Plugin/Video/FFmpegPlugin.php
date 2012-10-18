<?php

namespace Xi\Filelib\Plugin\Video;

use Symfony\Component\Process\Process;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;

class FFmpegPlugin extends AbstractVersionProvider implements VersionProvider
{
    protected $providesFor = array('video');

    public function createVersions(File $file)
    {
        //$ffmpeg = new Process("ffmpeg -i $file -vframes 1 thumb.jpg");
    }

    public function getExtensionFor($version)
    {
    }

    public function getVersions()
    {
        return array(); // @TODO calculate from options' outfiles
    }

    public function areSharedVersionsAllowed()
    {
    }

    public function isSharedResourceAllowed()
    {
    }

    public function getDuration(File $file)
    {
        return (float) $this->getVideoInfo($file)->format->duration;
    }

    public function getVideoInfo(File $file)
    {
        $path = $this->getStorage()->retrieve($file->getResource())->getPathname();

        $probe = new Process(sprintf("ffprobe -loglevel quiet -print_format json -show_format %s", $path));
        $probe->setTimeout(30);
        $probe->run();

        if (!$probe->isSuccessful()) {
            throw new RuntimeException($process->getErrorOutput());
        }
        return json_decode($probe->getOutput());
    }
}