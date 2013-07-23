<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Acl;

use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;

/**
 * ACL adapter interface
 *
 */
interface AclAdapter
{

    /**
     * Returns whether a file is readable
     *
     * @param File $file
     */
    public function isFileReadable(File $file);

    /**
     * Returns whether a file is writeable
     *
     * @param File $file
     */
    public function isFileWritable(File $file);

    /**
     * Returns whether a file is readable by anonymous user
     *
     * @param File $file
     */
    public function isFileReadableByAnonymous(File $file);

    /**
     * Returns whether a folder is readable
     *
     * @param Folder $folder
     */
    public function isFolderReadable(Folder $folder);

    /**
     * Returns whether a folder is writeable
     *
     * @param Folder $folder
     */
    public function isFolderWritable(Folder $folder);

    /**
     * Returns whether a folder is readable by anonymous user
     *
     * @param Folder $folder
     */
    public function isFolderReadableByAnonymous(Folder $folder);
}
