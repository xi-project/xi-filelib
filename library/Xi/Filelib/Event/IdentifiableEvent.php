<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Xi\Filelib\Identifiable;
use Symfony\Component\EventDispatcher\Event;

/**
 * Identifiable event
 */
class IdentifiableEvent extends Event
{
    /**
     * @var Identifiable
     */
    private $identifiable;

    /**
     * @param Identifiable $identifiable
     */
    public function __construct(Identifiable $identifiable)
    {
        $this->identifiable = $identifiable;
    }

    /**
     * @return Identifiable
     */
    public function getIdentifiable()
    {
        return $this->identifiable;
    }
}
