<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\Version;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;
use Xi\Filelib\Versionable;

class VersionProviderEvent extends Event
{
    /**
     * @var VersionProvider
     */
    private $provider;

    /**
     * @var Versionable
     */
    private $versionable;

    /**
     * @var array
     */
    private $versions;

    public function __construct(VersionProvider $provider, Versionable $versionable, $versions = array())
    {
        $this->provider = $provider;
        $this->versionable = $versionable;
        $this->versions = array_map(
            function ($version) {
                return Version::get($version);
            },
            $versions
        );
    }

    /**
     * @return VersionProvider
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return Versionable
     */
    public function getVersionable()
    {
        return $this->versionable;
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
    }
}
