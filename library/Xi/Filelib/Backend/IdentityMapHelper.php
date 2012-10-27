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
 * Identity map helper for backend
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

    public function tryOneFromIdentityMap($id, $class, $callable)
    {
        if ($ret = $this->getIdentityMap()->get($id, $class)) {
            return $ret;
        }

        $ret = $callable($this->getPlatform(), $id);

        $this->getIdentityMap()->addMany($ret);
        return $ret->current();
    }

    public function tryManyFromIdentityMap(array $ids, $class, $callable)
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

        $iter = $callable($this->getPlatform(), array_values($notFound));
        $this->getIdentityMap()->addMany($iter);

        $ret = array();
        foreach ($ids as $id) {
            if ($obj = $this->getIdentityMap()->get($id, $class)) {
                $ret[] = $obj;
            }
        }
        return new ArrayIterator($ret);

    }

    public function tryAndAddToIdentityMap($callable)
    {
        $args = func_get_args();
        array_shift($args);
        array_unshift($args, $this->getPlatform());
        $ret = call_user_func_array($callable, $args);
        $this->getIdentityMap()->add($ret);
        return $ret;
    }

    public function tryAndRemoveFromIdentityMap($callable, Identifiable $identifiable)
    {
        $ret = $callable($this->getPlatform(), $identifiable);
        $this->getIdentityMap()->remove($identifiable);
        return $ret;
    }





}
