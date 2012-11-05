<?php

namespace Xi\Filelib\Plugin\Video\FFmpeg;

use RuntimeException;
use Symfony\Component\Process\Process;
use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\Video\FFmpeg\FFmpegHelper;
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

    /**
     * Create image, audio and/or video versions using FFmpegHelper
     *
     * @param File $file
     * @return array
     */
    public function createVersions(File $file)
    {
        $retrieved = $this->getPathname($file);
        $tmpDir = $this->getFilelib()->getTempdir();

        $this->getHelper()->execute($retrieved, $tmpDir);

        return $this->getHelper()->getOutputPathnames($tmpDir);
    }

    /**
     * @inheritDoc
     */
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

    public function areSharedVersionsAllowed()
    {
        return true;
    }

    public function isSharedResourceAllowed()
    {
        return true;
    }

   private function getPathname(File $file)
    {
        return $this->getStorage()->retrieve($file->getResource())->getPathname();
    }

}