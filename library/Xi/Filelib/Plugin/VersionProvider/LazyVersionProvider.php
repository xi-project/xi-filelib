<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin\VersionProvider;
use Xi\Filelib\Event\FileEvent;

/**
 * Lazy version provider
 *
 * @author pekkis
 */
abstract class LazyVersionProvider extends VersionProvider
{
    /**
     * @var bool
     */
    private $lazyMode = false;

    /**
     * @param bool $enabled
     */
    public function enableLazyMode($enabled = true)
    {
        $this->lazyMode = $enabled;
    }

    /**
     * @return bool
     */
    public function lazyModeEnabled()
    {
        return $this->lazyMode;
    }

    /**
     * @param FileEvent $event
     */
    public function onAfterUpload(FileEvent $event)
    {
        if ($this->lazyModeEnabled()) {
            return;
        }
        parent::onAfterUpload($event);
    }

}
