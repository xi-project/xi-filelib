<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Publisher;

class Events
{
    const FILE_BEFORE_PUBLISH = 'xi_filelib.publisher.file.before_publish';
    const FILE_AFTER_PUBLISH = 'xi_filelib.publisher.file.after_publish';
    const FILE_BEFORE_UNPUBLISH = 'xi_filelib.publisher.file.before_unpublish';
    const FILE_AFTER_UNPUBLISH = 'xi_filelib.publisher.file.after_unpublish';
}
