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
    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $parameters = array();

    /**
     * @param string $method
     * @param array $parameters
     */
    public function __construct($method, $parameters = array())
    {
        $this->method = $method;

        if (!is_array($parameters)) {
            $parameters = array($parameters);
        }
        $this->parameters = $parameters;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param Imagick $imagick
     * @throws \BadMethodCallException
     */
    public function execute(Imagick $imagick)
    {
        $callable = array($imagick, $this->getMethod());

        if (!is_callable($callable)) {
            throw new BadMethodCallException(
                sprintf(
                    "Method '%s' not callable",
                    $this->getMethod()
                )
            );
        }

        call_user_func_array($callable, $this->getParameters());
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }
}
