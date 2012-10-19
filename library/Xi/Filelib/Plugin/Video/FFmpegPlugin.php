<?php

namespace Xi\Filelib\Plugin\Video;

use RuntimeException;
use Symfony\Component\Process\Process;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\VersionProvider\AbstractVersionProvider;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;

class FFmpegPlugin extends AbstractVersionProvider implements VersionProvider
{
    protected $providesFor = array('video');

    /* @var array */
    private $globalOptions = array();

    /* @var array */
    private $inputs = array();

    /* @var array */
    private $outputs = array();

    public function getGlobalOptions()
    {
        return $this->globalOptions;
    }

    public function setGlobalOptions($options)
    {
        $this->globalOptions = $options;
        return $this;
    }

    public function getInputs()
    {
        return $this->inputs;
    }

    public function setInputs($inputs)
    {
        $this->inputs = $inputs;
        return $this;
    }

    public function getOutputs()
    {
        return $this->outputs;
    }

    public function setOutputs($outputs)
    {
        $this->outputs = $outputs;
        return $this;
    }

    public static function shellArguments($options)
    {
        $args = array();
        reset($options);

        while (list($key, $value) = each($options)) {
            (true === $value)
                ? $args[] = "-$key"
                : $args[] = "-$key " . escapeshellarg($value);
        }

        return implode(' ', $args);
    }

    public function shellArgumentsFor($io, $prefix = '')
    {
        return FFmpegPlugin::shellArguments($io['options']) . ($prefix ? " $prefix " : ' ') . escapeshellarg($io['filename']);
    }

    public function getCommand()
    {
        return implode(' ', array(
            'ffmpeg',
            FFmpegPlugin::shellArguments($this->getGlobalOptions()),
            implode(' ', array_map(function($input) { return $this->shellArgumentsFor($input, '-i'); }, $this->getInputs())),
            implode(' ', array_map(array($this, 'shellArgumentsFor'), $this->getOutputs()))
        ));
    }

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

    public function getDuration(File $file)
    {
        return (float) $this->getVideoInfo($file)->format->duration;
    }

    public function getVideoInfo(File $file)
    {
        $path = $this->getStorage()->retrieve($file)->getPathname();

        $probe = new Process(sprintf("ffprobe -loglevel quiet -print_format json -show_format %s", $path));
        $probe->setTimeout(30);
        $probe->run();

        if (!$probe->isSuccessful()) {
            throw new RuntimeException($probe->getErrorOutput());
        }
        return json_decode($probe->getOutput());
    }
}