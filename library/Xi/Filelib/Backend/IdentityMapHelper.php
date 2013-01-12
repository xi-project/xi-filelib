<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend;

use Xi\Filelib\IdentityMap\IdentityMap;
use Xi\Filelib\IdentityMap\Identifiable;
use Xi\Filelib\Backend\Platform\Platform;
use ArrayIterator;

/**
 * Identity map backend helper
 */
class IdentityMapHelper
{
    /**
     * @var Platform
     */
    private $platform;

    /**
     * @var IdentityMap
     */
    private $identityMap;

    /**
     * @param IdentityMap $identityMap
     * @param Platform $platform
     */
    public function __construct(IdentityMap $identityMap, Platform $platform)
    {
        $this->identityMap = $identityMap;
        $this->platform = $platform;
    }

    /**
     * @return Platform
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @return IdentityMap
     */
    public function getIdentityMap()
    {
        return $this->identityMap;
    }

    /**
     * Tries to fetch one from identity map. Delegates to callback if fails.
     *
     * @param mixed $id
     * @param string $class
     * @param callable $callback
     * @return Identifiable|false
     */
    public function tryOneFromIdentityMap($id, $class, $callback)
    {
        if ($ret = $this->getIdentityMap()->get($id, $class)) {
            return $ret;
        }

        $ret = $callback($this->getPlatform(), $id);

        $this->getIdentityMap()->addMany($ret);
        return $ret->current();
    }

    /**
     * Tries to fetch many from identity map. Delegates to callback if fails.
     *
     * @param array $ids
     * @param string $class
     * @param callable $callback
     * @return ArrayIterator
     * @todo Extract methods
     */
    public function tryManyFromIdentityMap(array $ids, $class, $callback)
    {
        $found = array();
        foreach ($ids as $id) {
            if ($obj = $this->getIdentityMap()->get($id, $class)) {
                $found[$id] = $obj;
            }
        }

        $notFound = array_diff($ids, array_keys($found));
        if (!$notFound) {
            return new ArrayIterator($found);
        }

        $iter = $callback($this->getPlatform(), array_values($notFound));
        $this->getIdentityMap()->addMany($iter);

        $ret = array();
        foreach ($ids as $id) {
            if ($obj = $this->getIdentityMap()->get($id, $class)) {
                $ret[] = $obj;
            }
        }
        return new ArrayIterator($ret);

    }

    /**
     * Tries callback with mixed arguments. Adds return value to identity map.
     *
     * @param callable $callback
     * @return mixed
     */
    public function tryAndAddToIdentityMap($callback)
    {
        $args = func_get_args();
        array_shift($args);
        array_unshift($args, $this->getPlatform());
        $ret = call_user_func_array($callback, $args);
        $this->getIdentityMap()->add($ret);
        return $ret;
    }

    /**
     * Tries callback and removes from identity map.
     *
     * @param callable $callback
     * @param Identifiable $identifiable
     * @return mixed
     */
    public function tryAndRemoveFromIdentityMap($callback, Identifiable $identifiable)
    {
        $ret = $callback($this->getPlatform(), $identifiable);
        $this->getIdentityMap()->remove($identifiable);
        return $ret;
    }
}
