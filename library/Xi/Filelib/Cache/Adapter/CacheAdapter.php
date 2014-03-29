<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Cache\Adapter;

use Xi\Filelib\IdentityMap\Identifiable;

interface CacheAdapter
{
    /**
     * @param $id
     * @param $className
     * @return Identifiable
     */
    public function findById($id, $className);

    /**
     * @param array $ids
     * @param $className
     * @return Identifiable[]
     */
    public function findByIds(array $ids, $className);

    /**
     * @param Identifiable[] $identifiables
     */
    public function saveMany($identifiables);

    /**
     * @param Identifiable[] $identifiables
     */
    public function deleteMany($identifiables);

    /**
     * @param Identifiable $identifiable
     */
    public function save(Identifiable $identifiable);

    /**
     * @param Identifiable $identifiable
     * @return mixed
     */
    public function delete(Identifiable $identifiable);
}
