<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Xi\Filelib\Attacher;

/**
 * Xi Filelib plugin interface
 *
 * @package Xi_Filelib
 * @author pekkis
 */
interface Plugin extends EventSubscriberInterface, Attacher
{
    /**
     * Returns an array of profiles
     *
     * @return array
     */
    public function getProfiles();

    public function setProfiles(array $profiles);

    /**
     * Returns whether plugin has a certain profile
     *
     * @param  string  $profile
     * @return boolean
     */
    public function hasProfile($profile);
}
