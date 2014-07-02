<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Cache\Adapter;

use Xi\Filelib\Identifiable;

class NullCacheAdapter implements CacheAdapter
{
    /**
     * @param $id
     * @param $className
     * @return Identifiable
     */
    public function findById($id, $className)
    {
        return false;
    }

    /**
     * @param array $ids
     * @param $className
     * @return Identifiable[]
     */
    public function findByIds(array $ids, $className)
    {
        return array();
    }

    /**
     * @param Identifiable $identifiable
     */
    public function save(Identifiable $identifiable)
    {
        return null;
    }

    /**
     * @param Identifiable $identifiable
     * @return mixed
     */
    public function delete(Identifiable $identifiable)
    {
        return null;
    }

    public function clear()
    {
        return null;
    }
}
