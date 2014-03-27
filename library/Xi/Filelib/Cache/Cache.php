<?php

namespace Xi\Filelib\Cache;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Backend\FindByIdsRequestResolver;
use Xi\Filelib\Cache\Adapter\CacheAdapter;
use Xi\Filelib\Event\IdentifiableEvent;
use Xi\Filelib\Events;
use Xi\Filelib\IdentityMap\Identifiable;
use ArrayIterator;

class Cache implements FindByIdsRequestResolver, EventSubscriberInterface
{
    /**
     * @var CacheAdapter
     */
    private $adapter;

    public function __construct(CacheAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    public static function getSubscribedEvents()
    {
        return array(
            Events::FILE_AFTER_CREATE => 'onCreate',
            Events::FILE_AFTER_DELETE => 'onDelete',
            Events::FOLDER_AFTER_DELETE => 'onDelete',
            Events::FOLDER_AFTER_CREATE => 'onCreate',
            Events::IDENTIFIABLE_INSTANTIATE => 'onInstantiate',
            Events::FOLDER_AFTER_UPDATE => 'onUpdate',
            Events::FILE_AFTER_UPDATE => 'onUpdate'
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
        return $request->foundMany($identifiables);
    }

    /**
     * @param Identifiable[] $identifiables
     */
    public function saveMany($identifiables)
    {
        return $this->adapter->saveMany($identifiables);
    }

    /**
     * @param Identifiable[] $identifiables
     */
    public function deleteMany($identifiables)
    {
        return $this->adapter->deleteMany($identifiables);
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
     * @return mixed
     */
    public function delete(Identifiable $identifiable)
    {
        return $this->adapter->delete($identifiable);
    }

    public function onInstantiate(IdentifiableEvent $event)
    {
        $this->save($event->getIdentifiable());
    }

    public function onUpdate(IdentifiableEvent $event)
    {
        $this->save($event->getIdentifiable());
    }

    public function onDelete(IdentifiableEvent $event)
    {
        $this->delete($event->getIdentifiable());
    }

    public function onCreate(IdentifiableEvent $event)
    {
        $this->delete($event->getIdentifiable());
    }
}


