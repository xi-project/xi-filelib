<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Authorization\Adapter;

use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Authorization\AuthorizationAdapter;

/**
 * Simple ACL for development / testing / simplest of actual use cases
 *
 * @author pekkis
 *
 */
class SimpleAuthorizationAdapter implements AuthorizationAdapter
{
    /**
     * @var bool
     */
    private $folderReadableByAnonymous = true;

   /**
    * @var bool
    */
    private $fileReadableByAnonymous = true;

    /**
     * @var bool
     */
    private $fileReadable = true;

    /**
     * @var bool
     */
    private $fileWritable = true;

    /**
     * @var bool
     */
    private $folderReadable = true;

    /**
     * @var bool
     */
    private $folderWritable = true;

    /**
     * {@inheritdoc}
     */
    public function isFileReadable(File $file)
    {
        return $this->fileReadable;
    }

    /**
     * {@inheritdoc}
     */
    public function isFileWritable(File $file)
    {
        return $this->fileWritable;
    }

    /**
     * {@inheritdoc}
     */
    public function isFileReadableByAnonymous(File $file)
    {
        return $this->fileReadableByAnonymous;
    }

    /**
     * {@inheritdoc}
     */
    public function isFolderReadable(Folder $file)
    {
        return $this->folderReadable;
    }

    /**
     * {@inheritdoc}
     */
    public function isFolderWritable(Folder $file)
    {
        return $this->folderWritable;
    }

    /**
     * {@inheritdoc}
     */
    public function isFolderReadableByAnonymous(Folder $file)
    {
        return $this->folderReadableByAnonymous;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setFolderReadableByAnonymous($value)
    {
        $this->folderReadableByAnonymous = $value;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setFileReadableByAnonymous($value)
    {
        $this->fileReadableByAnonymous = $value;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setFileReadable($value)
    {
        $this->fileReadable = $value;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setFileWritable($value)
    {
        $this->fileWritable = $value;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setFolderReadable($value)
    {
        $this->folderReadable = $value;

        return $this;
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setFolderWritable($value)
    {
        $this->folderWritable = $value;

        return $this;
    }
}
