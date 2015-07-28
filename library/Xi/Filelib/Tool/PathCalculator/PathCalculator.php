<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Tool\PathCalculator;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Versionable\Version;
use Xi\Filelib\Versionable\Versionable;

interface PathCalculator
{
    /**
     * @param Resource $resource
     * @return string
     */
    public function getPath(Resource $resource);

    /**
     * @param Versionable $versionable
     * @param Version $version
     * @return string
     */
    public function getPathVersion(Versionable $versionable, Version $version);
}
