<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Xi Filelib plugin interface
 *
 * @package Xi_Filelib
 * @author pekkis
 */
interface Plugin extends EventSubscriberInterface
{
    /**
     * Returns an array of profiles
     *
     * @return array
     */
    public function getProfiles();

    /**
     * Returns whether plugin has a certain profile
     *
     * @param  string  $profile
     * @return boolean
     */
    public function hasProfile($profile);

    /**
     * Runs when plugin is added.
     */
    public function init();
}
