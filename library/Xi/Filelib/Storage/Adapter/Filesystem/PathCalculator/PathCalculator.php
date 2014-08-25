<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage\Adapter\Filesystem\PathCalculator;

use Xi\Filelib\Resource\Resource;
use Xi\Filelib\Version;
use Xi\Filelib\Versionable;

interface PathCalculator
{
    public function getPath(Resource $resource);

    public function getPathVersion(Versionable $versionable, Version $version);
}
