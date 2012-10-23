<?php

namespace Xi\Filelib\Plugin\Video;

use RuntimeException;
use Symfony\Component\Process\Process;
use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\FileObject;

class FFmpegHelper
{
    /* @var string */
    private $command = 'ffmpeg';

    /* @var array */
    private $globalOptions = array();

    /* @var array */
    private $inputs = array();

    /* @var array */
    private $outputs = array();

    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }

    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string $command The path of the command line command
     */
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

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
        return FFmpegHelper::shellArguments($io['options']) . ($prefix ? " $prefix " : ' ') . escapeshellarg($io['filename']);
    }

    public function getCommandLine()
    {
        return implode(' ', array_merge(
            array('ffmpeg'),
            array(FFmpegHelper::shellArguments($this->getGlobalOptions())),
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

    public function getDuration(FileObject $file)
    {
        return (float) $this->getVideoInfo($file)->format->duration;
    }

    public function getVideoInfo(FileObject $file)
    {
        return json_decode(
            $this->runProcess(
                sprintf(
                    "ffprobe -loglevel quiet -print_format json -show_streams -show_format %s",
                    $file->getPathname()
                )
            )
        );
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