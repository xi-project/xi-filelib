<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony\Serializer;

use Xi\Filelib\Identifiable;

class SerializedIdentifiable
{
    public $className;

    public $id;

    public function __construct(Identifiable $identifiable)
    {
        $this->className = get_class($identifiable);
        $this->id = $identifiable->getId();
    }
}