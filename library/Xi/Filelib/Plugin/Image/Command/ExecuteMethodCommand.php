<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\Image\Command;

use Imagick;
use BadMethodCallException;

class ExecuteMethodCommand extends AbstractCommand
{
    private $method = null;
    private $parameters = array();


    public function __construct($method, $parameters = array())
    {
        $this->method = $method;

        if (!is_array($parameters)) {
            $parameters = array($parameters);
        }

        $this->parameters = $parameters;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function execute(Imagick $imagick)
    {
        $callable = array($imagick, $this->getMethod());

        if (!is_callable($callable)) {
            throw new BadMethodCallException(sprintf(
                "Method '%s' not callable", $this->getMethod()
            ));
        }

        call_user_func_array($callable, $this->getParameters());
    }
}
