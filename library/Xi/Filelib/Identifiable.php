<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib;

use Xi\Filelib\IdentifiableDataContainer;

/**
 * Interface for identifiable objects
 */
interface Identifiable
{
    public function getId();

    /**
     * @return IdentifiableDataContainer
     */
    public function getData();

    /**
     * @param IdentifiableDataContainer|array $data
     * @return Identifiable
     */
    public function setData($data);
}
