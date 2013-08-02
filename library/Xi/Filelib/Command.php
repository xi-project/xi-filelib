<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\Attacher;
use Symfony\Component\Console\Output\OutputInterface;

interface Command extends Attacher
{
    public function execute();

    /**
     * @param OutputInterface $output
     * @return Command
     */
    public function setOutput(OutputInterface $output);

    /**
     * @return OutputInterface
     */
    public function getOutput();
}
