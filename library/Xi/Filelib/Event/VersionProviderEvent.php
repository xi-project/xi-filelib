<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Event;

use Symfony\Component\EventDispatcher\Event;
use Xi\Filelib\File\File;
use Xi\Filelib\Version;
use Xi\Filelib\Plugin\VersionProvider\VersionProvider;

class VersionProviderEvent extends Event
{
    /**
     * @var VersionProvider
     */
    private $provider;

    /**
     * @var File
     */
    private $file;

    /**
     * @var array
     */
    private $versions;

    public function __construct(VersionProvider $provider, File $file, $versions = array())
    {
        $this->provider = $provider;
        $this->file = $file;
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
     * @return File
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return array
     */
    public function getVersions()
    {
        return $this->versions;
    }
}
