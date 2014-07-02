<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Authorization\Adapter;

use Xi\Filelib\Authorization\AuthorizationAdapter;
use Xi\Filelib\File\File;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\Folder\Folder;

/**
 * Simple ACL for development / testing / simplest of actual use cases
 *
 * @author pekkis
 *
 */
class SimpleAuthorizationAdapter implements AuthorizationAdapter
{
    /**
     * @var callable
     */
    private $folderReadableByAnonymous;

    /**
     * @var callable
     */
    private $fileReadableByAnonymous;

    /**
     * @var callable
     */
    private $fileReadable;

    /**
     * @var callable
     */
    private $fileWritable;

    /**
     * @var callable
     */
    private $folderReadable;

    /**
     * @var callable
     */
    private $folderWritable;

    public function __construct()
    {
        $this->setFileReadable(true);
        $this->setFileReadableByAnonymous(true);
        $this->setFileWritable(true);
        $this->setFolderReadable(true);
        $this->setFolderWritable(true);
        $this->setFolderReadableByAnonymous(true);
    }

    /**
     * {@inheritdoc}
     */
    public function isFileReadable(File $file)
    {
        return call_user_func($this->fileReadable, $file);
    }

    /**
     * {@inheritdoc}
     */
    public function isFileWritable(File $file)
    {
        return call_user_func($this->fileWritable, $file);
    }

    /**
     * {@inheritdoc}
     */
    public function isFileReadableByAnonymous(File $file)
    {
        return call_user_func($this->fileReadableByAnonymous, $file);
    }

    /**
     * {@inheritdoc}
     */
    public function isFolderReadable(Folder $file)
    {
        return call_user_func($this->folderReadable, $file);
    }

    /**
     * {@inheritdoc}
     */
    public function isFolderWritable(Folder $file)
    {
        return call_user_func($this->folderWritable, $file);
    }

    /**
     * {@inheritdoc}
     */
    public function isFolderReadableByAnonymous(Folder $file)
    {
        return call_user_func($this->folderReadableByAnonymous, $file);
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setFolderReadableByAnonymous($value)
    {
        $this->folderReadableByAnonymous = $this->wrap($value);

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setFileReadableByAnonymous($value)
    {
        $this->fileReadableByAnonymous = $this->wrap($value);

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setFileReadable($value)
    {
        $this->fileReadable = $this->wrap($value);

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setFileWritable($value)
    {
        $this->fileWritable = $this->wrap($value);

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setFolderReadable($value)
    {
        $this->folderReadable = $this->wrap($value);

        return $this;
    }

    /**
     * @param mixed $value
     * @return $this
     */
    public function setFolderWritable($value)
    {
        $this->folderWritable = $this->wrap($value);

        return $this;
    }

    /**
     * @param mixed $value
     * @return callable
     */
    private function wrap($value)
    {
        if (!is_callable($value)) {
            return function () use ($value) {
                return $value;
            };
        }
        return $value;
    }

    public function attachTo(FileLibrary $filelib)
    {
    }
}
