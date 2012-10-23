<?php

namespace Xi\Filelib\Plugin\Video;

use RuntimeException;
use Symfony\Component\Process\Process;
use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\Video\FFmpegHelper;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;

class FFmpegPlugin extends AbstractVersionProvider implements VersionProvider
{
    protected $providesFor = array('video');

    protected $helper;

    public function __construct($options = array())
    {
        parent::__construct($options);
        Configurator::setOptions($this->getHelper(), $options);
    }

    /**
     * @return FFmpegHelper
     */
    public function getHelper()
    {
        if (!$this->helper) {
            $this->helper = new FFmpegHelper();
        }
        return $this->helper;
    }

    public function createVersions(File $file)
    {
        $path = $this->getPathname($file);

        $this->runProcess($this->getCommand(), 0);

        // return $this->storeOutputs();
    }

    public function getExtensionFor($version)
    {
        return pathinfo($this->getHelper()->getOutputs()[$version]['filename'], PATHINFO_EXTENSION);
    }

    /**
     * Returns an array of (potentially) provided versions
     *
     * @return array
     */
    public function getVersions()
    {
        // @TODO calculate output filenames from ffmpeg options (it's complicated),
        // and enable producing multiple output files (file resources) per version
        return array_keys($this->getHelper()->getOutputs());
    }

    private function getPathname(File $file)
    {
        return $this->getStorage()->retrieve($file)->getPathname();
    }

}