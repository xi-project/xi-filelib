<?php

namespace Xi\Filelib\Plugin\Video;

use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\ConsoleHelper;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;

class FFmpegPlugin extends AbstractVersionProvider implements VersionProvider
{
    protected $providesFor = array('video');

    public function createVersions(File $file)
    {
        $ffmpeg = new ConsoleHelper('ffmpeg', 'en_US.UTF-8');
    }

    public function getExtensionFor($version)
    {
    }

    public function getVersions()
    {
        return array(); // @TODO calculate from options' outfiles
    }
}