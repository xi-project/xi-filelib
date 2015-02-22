<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony\Serializer;

class SerializedCallback
{
    public $callback;

    public $params;

    public function __construct($callback, $params)
    {
        $this->callback = $callback;
        $this->params = $params;
    }

}
