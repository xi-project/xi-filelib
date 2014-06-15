<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Backend;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\Event\IdentifiableEvent;
use Xi\Filelib\Identifiable;
use Traversable;
use Xi\Filelib\Events;

class FindByIdsRequest
{
    /**
     * @var array
     */
    private $notFoundIds = array();

    /**
     * @var array
     */
    private $foundIds = array();

    /**
     * @var array
     */
    private $foundObjects = array();

    /**
     * @var bool
     */
    private $isOrigin = false;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param mixed $ids
     * @param string $className
     * @param EventDispatcherInterface $eventDispatcher
     */
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

    /**
     * @return bool
     */
    public function isFulfilled()
    {
        return count($this->notFoundIds) === 0;
    }

    /**
     * @return array
     */
    public function getNotFoundIds()
    {
        return $this->notFoundIds;
    }

    /**
     * @return array
     */
    public function getFoundIds()
    {
        return $this->foundIds;
    }

    /**
     * @return string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * @return ArrayCollection
     */
    public function getResult()
    {
        return ArrayCollection::create($this->foundObjects);
    }

    /**
     * @param Identifiable $identifiable
     * @return FindByIdsRequest
     */
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
     * @return FindByIdsRequest
     */
    public function resolve(array $resolvers)
    {
        foreach ($resolvers as $resolver) {
            $this->isOrigin($resolver->isOrigin());
            $resolver->findByIds($this);
        }
        return $this;
    }

    /**
     * @param bool $isOrigin
     */
    public function isOrigin($isOrigin)
    {
        $this->isOrigin = $isOrigin;
    }
}
