<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractCommand implements Command
{
    /**
     * @var string
     */
    protected $uuid;

    protected $output;

    /**
     * @param string $uuid
     */
    public function __construct()
    {
        $this->uuid = Uuid::uuid4()->toString();
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @return string
     */
    public function getEnqueueReturnValue()
    {
        return $this->getUuid();
    }

    /**
     * @param OutputInterface $output
     * @return Command
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput()
    {
        if (!$this->output) {
            $this->output = new NullOutput();
        }

        return $this->output;
    }
}
