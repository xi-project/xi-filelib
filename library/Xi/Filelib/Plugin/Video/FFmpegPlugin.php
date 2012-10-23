<?php

namespace Xi\Filelib\Plugin\Video;

use RuntimeException;
use Symfony\Component\Process\Process;
use Xi\Filelib\Exception\InvalidArgumentException;
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
        foreach ($outputs as $output) {
            if (array_key_exists('filename', $output) && ('.' !== dirname($output['filename']))) {
                throw new InvalidArgumentException('Output filenames must not contain paths.');
            }
        }
        $this->outputs = $outputs;
        return $this;
    }

    public function getExtensionFor($version)
    {
        return pathinfo($this->getOutputs()[$version]['filename'], PATHINFO_EXTENSION);
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
        return array_keys($this->getOutputs());
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
        return implode(' ', array_merge(
            array('ffmpeg'),
            array(FFmpegPlugin::shellArguments($this->getGlobalOptions())),
            array_map(
                function($input) {
                    return $this->shellArgumentsFor($input, '-i');
                },
                $this->getInputs()
            ),
            array_map(
                array($this, 'shellArgumentsFor'),
                $this->getOutputs()
            )
        ));
    }

    public function createVersions(File $file)
    {
        $path = $this->getPathname($file);

        $this->runProcess($this->getCommand(), 0);

        // return $this->storeOutputs();
    }

    public function getDuration(File $file)
    {
        return (float) $this->getVideoInfo($file)->format->duration;
    }

    public function getVideoInfo(File $file)
    {
        return json_decode(
            $this->runProcess(
                sprintf(
                    "ffprobe -loglevel quiet -print_format json -show_streams -show_format %s",
                    $this->getPathname($file)
                )
            )
        );
    }

    private function getPathname(File $file)
    {
        return $this->getStorage()->retrieve($file)->getPathname();
    }

    private function runProcess($cmd, $timeout = 30)
    {
        $proc = new Process($cmd);
        if ($timeout) { $proc->setTimeout($timeout); }
        $proc->run();

        if (!$proc->isSuccessful()) {
            throw new RuntimeException($proc->getErrorOutput());
        }
        return $proc->getOutput();
    }
}