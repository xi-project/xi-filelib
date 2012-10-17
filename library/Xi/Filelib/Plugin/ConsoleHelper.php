<?php

namespace Xi\Filelib\Plugin;

use Xi\Filelib\Exception\SubprocessException;
use Xi\Filelib\Exception\InvalidArgumentException;

class ConsoleHelper
{
    /**
     * @var string $cmd The command to execute
     */
    private $cmd;

    /**
     * @var string $locale The locale to use with string handling functions
     */
    private $locale;

    /**
     * @param string $cmd Command name -- must only include alphabetical characters, dots, slashes, hyphens and underscores and not include spaces
     * @param string $locale The locale to use with the command
     */
    public function __construct($cmd, $locale = null)
    {
        if (!preg_match('/^[[:alnum:]][[:alnum:]-_]+$/u', $cmd)) {
            throw new InvalidArgumentException('Command name must start with an alphanumerical character, and after that include only alphanumerical characters, hyphens or underscores and not include any spaces.');
        }

        if (!$this->locale = setlocale(LC_ALL, $locale)) {
            throw new InvalidArgumentException(sprintf("Failed to set locale '%s'", $locale));
        }

        $this->cmd = $cmd;
    }

    /**
     * Execute a command with arguments and input. Get output lines as an array.
     *
     * @param string $cmd
     * @param string $input
     * @throws Exception If the command writes to stderr or it's return code is not zero
     * @return array Output lines from the shell command
     */
    public function execute($args, $input='')
    {
        // @TODO Use variable arg list, escape each argument and move input arg to setInput().
        $cmd = $this->cmd . ' ' . $args;

        $proc = proc_open(
            $cmd,
            array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w')
            ),
            $pipes
        );

        fwrite($pipes[0], $input); fclose($pipes[0]);
        $stdout = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $stderr = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $ret = proc_close($proc);

        if ($stderr) {
            throw new SubprocessException(sprintf("Command execution had the following errors: %s", $stderr));
        }

        if (0 !== $ret) {
            throw new SubprocessException(sprintf("Command '%s' returned %s.", $cmd, $ret));
        }

        return explode("\n", trim($stdout));
    }
}