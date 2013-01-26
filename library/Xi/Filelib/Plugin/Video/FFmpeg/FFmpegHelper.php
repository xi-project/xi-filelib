<?php

namespace Xi\Filelib\Plugin\Video\FFmpeg;

use RuntimeException;
use Symfony\Component\Process\Process;
use Xi\Filelib\Exception\InvalidArgumentException;
use Xi\Filelib\Configurator;
use Xi\Filelib\File\FileObject;

class FFmpegHelper
{
    /**
     * @var string
     */
    private $command = 'ffmpeg';

    /**
     * @var array
     */
    private $options = array();

    /**
     * @var array
     */
    private $inputs = array();

    /**
     * @var array
     */
    private $outputs = array();

    public function __construct($options = array())
    {
        Configurator::setConstructorOptions($this, $options);
    }

    /**
     * Get the path of the command line command for ffmpeg
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set the path of the command line command for ffmpeg
     * @param  string                   $command
     * @return FFmpegHelper
     * @throws InvalidArgumentException
     */
    public function setCommand($command)
    {
        if (!$command) {
            throw new InvalidArgumentException('Command must not be empty.');
        }
        $this->command = $command;

        return $this;
    }

    /**
     * Get the global options for ffmpeg
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Set the global options for ffmpeg
     *
     * @param array @options
     * @return FFmpegHelper
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Get an array of input filenames and their options
     * @return array
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * Set an array of input filenames and their options
     *
     * @param array @inputs
     * @return FFmpegHelper
     */
    public function setInputs($inputs)
    {
        $this->inputs = $inputs;

        return $this;
    }

    /**
     * Get an array of output filenames and their options
     * @return array
     */
    public function getOutputs()
    {
        return $this->outputs;
    }

    /**
     * Set an array of output filenames and their options.
     * Filenames must not contain paths (slashes).
     * Number templated filenames (%d) are not supported at this time.
     *
     * @param  array                    $outputs
     * @return FFmpegHelper
     * @throws InvalidArgumentException
     */
    public function setOutputs($outputs)
    {
        foreach ($outputs as $output) {
            if (array_key_exists('filename', $output)) {
                if ('.' !== dirname($output['filename'])) {
                    throw new InvalidArgumentException('Output filenames must not contain paths.');
                }
                if (preg_match('/%(0\d+)?d/u', $output['filename'])) {
                    throw new InvalidArgumentException('Number templated filenames are not supported.');
                }
            }
        }
        $this->outputs = $outputs;

        return $this;
    }

    /**
     * Execute ffmpeg processing in a subprocess.
     *
     * @param string @original Filename to replace default values (true) on inputs
     * @param string @outputDir Path to where put the outputs
     * @return string Output from the command
     */
    public function execute($original, $outputDir)
    {
        $timeout = 3600; // 1 hour

        return $this->runProcess($this->getCommandLine($original, $outputDir), $timeout);
    }

    /**
     * Construct the ffmpeg command line from options and parameters
     *
     * @param string $original Pathname to original file
     * @param string @outputDir Directory to place processed outputs
     */
    public function getCommandLine($original, $outputDir)
    {
        $self = $this;

        return implode(' ', array_merge(
            array($this->getCommand()),
            array(FFmpegHelper::shellArguments($this->getOptions())),
            array_map(
                function ($input) use ($self) {
                    return $self->shellArgumentsFor($input, '-i');
                },
                $this->getProcessedInputs($original)
            ),
            array_map(
                array($this, 'shellArgumentsFor'),
                $this->getProcessedOutputs($outputDir)
            ),
            array('</dev/null')
        ));
    }

    /**
     * Replaces the default filenames (boolean true) with the string $original in inputs
     *
     * @param string original
     * @return array
     */
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

    /**
     * Prepends the output directory to the output filenames
     *
     * @param  string $outputDir
     * @return array
     */
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

    /**
     * Return a map of output keys to their absolute paths in the $outputDir
     *
     * @param  string $outputDir
     * @return array
     */
    public function getOutputPathnames($outputDir)
    {
        return array_map(
            function ($output) use ($outputDir) {
                return realpath($outputDir) .'/'. $output['filename'];
            },
            $this->getOutputs()
        );
    }

    /**
     * Generate command line options for ffmpeg from one $options array
     *
     * @param  array  $options
     * @return string
     */
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

    /**
     * Generate command line options for one input (use $prefix "-i") or output file from $io array
     *
     * @param  array  $io
     * @param  string $prefix
     * @return string
     */
    public function shellArgumentsFor($io, $prefix = '')
    {
        return FFmpegHelper::shellArguments($io['options']) . ($prefix ? " $prefix " : ' ') . escapeshellarg($io['filename']);
    }

    /**
     * Get the duration of a video using ffprobe
     *
     * @param  FileObject $file
     * @return float
     */
    public function getDuration(FileObject $file)
    {
        return (float) $this->getVideoInfo($file)->format->duration;
    }

    /**
     * Get information about video format and it's streams
     *
     * @param  FileObject $file
     * @return stdClass
     */
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

    /**
     * Run a command in subprocess using Symfony Process component.
     *
     * @param  string           $cmd
     * @param  int              $timeout Time limit for the command to run. Run forever with 0.
     * @return string           Command output
     * @throws RuntimeException
     */
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
