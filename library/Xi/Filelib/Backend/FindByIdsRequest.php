<?php

namespace Xi\Filelib\Backend;

use ArrayIterator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Filelib\Event\IdentifiableEvent;
use Xi\Filelib\IdentityMap\Identifiable;
use Traversable;
use Xi\Filelib\Events;

class FindByIdsRequest
{
    private $notFoundIds = array();

    private $foundIds = array();

    private $foundObjects = array();

    private $isOrigin = false;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct($ids, $className, EventDispatcherInterface $eventDispatcher = null)
    {
        if (!is_array($ids)) {
            $ids = array($ids);
        }

        $this->notFoundIds = $ids;
        $this->foundIds = array();
        $this->className = $className;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function isFulfilled()
    {
        return count($this->notFoundIds) === 0;
    }

    public function getNotFoundIds()
    {
        return $this->notFoundIds;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getResult()
    {
        return new ArrayIterator($this->foundObjects);
    }

    public function found(Identifiable $identifiable)
    {
        $key = array_search($identifiable->getId(), $this->notFoundIds);
        if ($key !== null) {
            unset($this->notFoundIds[$key]);
            $this->foundIds[] = $identifiable->getId();
            $this->foundObjects[] = $identifiable;

            if ($this->isOrigin && $this->eventDispatcher) {
                $this->eventDispatcher->dispatch(
                    Events::IDENTIFIABLE_INSTANTIATE,
                    new IdentifiableEvent($identifiable)
                );
            }
        }
        return $this;
    }

    /**
     * @param Identifiable[] $identifiables
     * @todo optimize
     */
    public function foundMany(Traversable $identifiables)
    {
        foreach ($identifiables as $identifiable) {
            $this->found($identifiable);
        }

        return $this;
    }

    /**
     * @param FindByIdsRequestResolver[] $resolvers
     * @return $this
     */
    public function resolve(array $resolvers)
    {
        foreach ($resolvers as $resolver) {
            $this->isOrigin($resolver->isOrigin());
            $resolver->findByIds($this);
        }
        return $this;
    }

    private function isOrigin($isOrigin)
    {
        $this->isOrigin = $isOrigin;
    }
}
