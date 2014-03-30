<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\IdentityMap;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Backend\FindByIdsRequest;
use Xi\Filelib\Backend\FindByIdsRequestResolver;
use Xi\Filelib\Event\IdentifiableEvent;
use Iterator;
use Xi\Filelib\Events;

/**
 * Identity map
 */
class IdentityMap implements EventSubscriberInterface, FindByIdsRequestResolver
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var array
     */
    private $objectIdentifiers = array();

    /**
     * @var array
     */
    private $objects = array();

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $eventDispatcher->addSubscriber($this);
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::FILE_AFTER_CREATE => 'onCreate',
            Events::FILE_AFTER_DELETE => 'onDelete',
            Events::FOLDER_AFTER_DELETE => 'onDelete',
            Events::FOLDER_AFTER_CREATE => 'onCreate',
            Events::RESOURCE_AFTER_CREATE => 'onCreate',
            Events::RESOURCE_AFTER_DELETE => 'onDelete'
        );
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * Returns whether identity map has an identifiable
     *
     * @param  Identifiable $object
     * @return bool
     */
    public function has(Identifiable $object)
    {
        return isset($this->objectIdentifiers[spl_object_hash($object)]);
    }

    /**
     * Adds an identifiable to identity map
     *
     * @param  Identifiable         $object
     * @throws IdentityMapException
     * @return bool
     */
    public function add(Identifiable $object)
    {
        if ($this->has($object)) {
            return false;
        }

        if (!$object->getId()) {
            throw new IdentityMapException("Trying to add a file without id to identity map");
        }

        $this->dispatchEvent($object, 'before_add');

        $identifier = $this->getIdentifierFromObject($object);
        $this->objectIdentifiers[spl_object_hash($object)] = $identifier;
        $this->objects[$identifier] = $object;

        $this->dispatchEvent($object, 'after_add');

        return true;
    }

    /**
     * Adds many identifiables to identity map
     *
     * @param Iterator $iterator
     */
    public function addMany(Iterator $iterator)
    {
        foreach ($iterator as $object) {
            $this->add($object);
        }
        $iterator->rewind();
    }

    /**
     * Removes many identifiables from identity map
     *
     * @param Iterator $iterator
     */
    public function removeMany(Iterator $iterator)
    {
        foreach ($iterator as $object) {
            $this->remove($object);
        }
        $iterator->rewind();
    }
    /**
     * Removes an identifiable
     *
     * @param  Identifiable $object
     * @return bool
     */
    public function remove(Identifiable $object)
    {
        $splHash = spl_object_hash($object);

        if (!isset($this->objectIdentifiers[$splHash])) {
            return false;
        }

        $this->dispatchEvent($object, 'before_remove');

        unset($this->objects[$this->objectIdentifiers[$splHash]]);
        unset($this->objectIdentifiers[$splHash]);

        $this->dispatchEvent($object, 'after_remove');

        return true;
    }

    /**
     * Gets an identifiable by id and class name
     *
     * @param  mixed              $id
     * @param  string             $className
     * @return Identifiable|false
     */
    public function get($id, $className)
    {
        $identifier = $this->getIdentifier($id, $className);

        if (!isset($this->objects[$identifier])) {
            return false;
        }

        return $this->objects[$identifier];
    }

    /**
     * @param IdentifiableEvent $event
     */
    public function onCreate(IdentifiableEvent $event)
    {
        $this->add($event->getIdentifiable());
    }

    /**
     * @param IdentifiableEvent $event
     */
    public function onInstantiate(IdentifiableEvent $event)
    {
        $this->add($event->getIdentifiable());
    }

    /**
     * @param IdentifiableEvent $event
     */
    public function onDelete(IdentifiableEvent $event)
    {
        $this->remove($event->getIdentifiable());
    }

    /**
     * @param FindByIdsRequest $request
     * @return FindByIdsRequest
     */
    public function findByIds(FindByIdsRequest $request)
    {
        $className = $request->getClassName();
        foreach ($request->getNotFoundIds() as $id) {
            if ($identifiable = $this->get($id, $className)) {
                $request->found($identifiable);
            }
        }
        return $request;
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
     * @return string
     */
    protected function getIdentifier($id, $className)
    {
        return $className . ' ' . $id;
    }

    /**
     * @param  Identifiable $object
     * @return string
     */
    protected function getIdentifierFromObject(Identifiable $object)
    {
        return get_class($object) . ' ' . $object->getId();
    }

    /**
     * @param Identifiable $object
     * @param $eventName
     */
    protected function dispatchEvent(Identifiable $object, $eventName)
    {
        $event = new IdentifiableEvent($object);
        $this->getEventDispatcher()->dispatch('xi_filelib.identitymap.' . $eventName, $event);
    }
}
