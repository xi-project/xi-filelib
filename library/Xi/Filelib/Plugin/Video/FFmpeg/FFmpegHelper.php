<?php

namespace Xi\Filelib\Plugin\Video\FFmpeg;

use RuntimeException;
use Symfony\Component\Process\Process;
use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\Exception\NotImplementedException;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\FileObject;

class FFmpegHelper
{
    /* @var string */
    private $command = 'ffmpeg';

    /* @var array */
    private $options = array();

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
        if (!$command) {
            throw new InvalidArgumentException('Command must not be empty.');
        }
        $this->command = $command;
        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }

    public function setOptions($options)
    {
        $this->options = $options;
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
            if (array_key_exists('filename', $output)) {
                if ('.' !== dirname($output['filename'])) {
                    throw new InvalidArgumentException('Output filenames must not contain paths.');
                }
                if (preg_match('/%(0\d+)?d/u', $output['filename'])) {
                    throw new NotImplementedException('Number templated filenames are not supported.');
                }
            }
        }
        $this->outputs = $outputs;
        return $this;
    }

    public function execute($original, $outputDir)
    {
        $timeout = 3600; // 1 hour
        return $this->runProcess($this->getCommandLine($original, $outputDir), $timeout);
    }

    /**
     * @param string $original Pathname to original file
     * @param string @outputDir Directory to place processed outputs
     */
    public function getCommandLine($original, $outputDir)
    {
        return implode(' ', array_merge(
            array($this->getCommand()),
            array(FFmpegHelper::shellArguments($this->getOptions())),
            array_map(
                function($input) {
                    return $this->shellArgumentsFor($input, '-i');
                },
                $this->getProcessedInputs($original)
            ),
            array_map(
                array($this, 'shellArgumentsFor'),
                $this->getProcessedOutputs($outputDir)
            )
        ));
    }

    private function getProcessedInputs($original)
    {
        return array_map(
            function ($input) use ($original) {
                return array(
                    'filename' => (
                        (!array_key_exists('filename', $input) ||
                         array_key_exists('filename', $input) && $input['filename'] === true) ?
                        $original :
                        $input['filename']
                    ),
                    'options' => $input['options']
                );
            },
            $this->getInputs()
        );
    }

    private function getProcessedOutputs($outputDir)
    {
        return array_map(
            function ($output) use ($outputDir) {
                return array(
                    'filename' => realpath($outputDir) .'/'. $output['filename'],
                    'options' => $output['options']
                );
            },
            $this->getOutputs()
        );
    }

    public function getOutputPathnames($outputDir)
    {
        return array_map(
            function ($output) use ($outputDir) {
                return realpath($outputDir) .'/'. $output['filename'];
            },
            $this->getOutputs()
        );
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
