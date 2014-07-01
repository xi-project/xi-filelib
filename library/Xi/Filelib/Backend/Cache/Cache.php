<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend\Cache;

use ArrayIterator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Xi\Filelib\Backend\Cache\Adapter\CacheAdapter;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Backend\FindByIdsRequestResolver;
use Xi\Filelib\Event\IdentifiableEvent;
use Xi\Filelib\Events;
use Xi\Filelib\Identifiable;

class Cache implements FindByIdsRequestResolver, EventSubscriberInterface
{
    /**
     * @var CacheAdapter
     */
    private $adapter;

    /**
     * @param CacheAdapter $adapter
     */
    public function __construct(CacheAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @return CacheAdapter
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::FILE_AFTER_CREATE => 'onCreate',
            Events::FILE_AFTER_UPDATE => 'onUpdate',
            Events::FILE_AFTER_DELETE => 'onDelete',
            Events::FOLDER_AFTER_CREATE => 'onCreate',
            Events::FOLDER_AFTER_UPDATE => 'onUpdate',
            Events::FOLDER_AFTER_DELETE => 'onDelete',
            Events::RESOURCE_AFTER_CREATE => 'onCreate',
            Events::RESOURCE_AFTER_UPDATE => 'onUpdate',
            Events::RESOURCE_AFTER_DELETE => 'onDelete',
            Events::IDENTIFIABLE_INSTANTIATE => 'onInstantiate',
        );
    }

    /**
     * @return bool
     */
    public function isOrigin()
    {
        return false;
    }

    /**
     * @param $id
     * @param $className
     * @return Identifiable
     */
    public function findById($id, $className)
    {
        return $this->adapter->findById($id, $className);
    }

    /**
     * @param array $ids
     * @param $className
     * @return Identifiable[]
     */
    public function findByIds(FindByIdsRequest $request)
    {
        $identifiables = new ArrayIterator(
            $this->adapter->findByIds($request->getNotFoundIds(), $request->getClassName())
        );
        return $request->foundMany($identifiables) ?: array();
    }

    /**
     * @param Identifiable $identifiable
     */
    public function save(Identifiable $identifiable)
    {
        return $this->adapter->save($identifiable);
    }

    /**
     * @param Identifiable $identifiable
     */
    public function delete(Identifiable $identifiable)
    {
        return $this->adapter->delete($identifiable);
    }

    /**
     * @param IdentifiableEvent $event
     */
    public function onInstantiate(IdentifiableEvent $event)
    {
        $this->save($event->getIdentifiable());
    }

    /**
     * @param IdentifiableEvent $event
     */
    public function onUpdate(IdentifiableEvent $event)
    {
        $this->save($event->getIdentifiable());
    }

    /**
     * @param IdentifiableEvent $event
     */
    public function onDelete(IdentifiableEvent $event)
    {
        $this->delete($event->getIdentifiable());
    }

    /**
     * @param IdentifiableEvent $event
     */
    public function onCreate(IdentifiableEvent $event)
    {
        $this->save($event->getIdentifiable());
    }

    public function clear()
    {
        $this->adapter->clear();
    }
}
