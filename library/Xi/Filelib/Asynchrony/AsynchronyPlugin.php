<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Asynchrony;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Plugin\BasePlugin;

class AsynchronyPlugin extends BasePlugin
{
    /**
     * @var Asynchrony
     */
    private $asynchrony;

    public function __construct(Asynchrony $asynchrony)
    {
        $this->asynchrony = $asynchrony;
    }

    public function attachTo(FileLibrary $filelib)
    {
        $filelib->setFileRepository(
            new FileRepository(
                $filelib->getFileRepository(),
                $this->asynchrony
            )
        );
    }

}
