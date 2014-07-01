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
     * @var bool
     */
    private $folderReadableByAnonymous;

    /**
     * @var bool
     */
    private $fileReadableByAnonymous;

    /**
     * @var bool
     */
    private $fileReadable;

    /**
     * @var bool
     */
    private $fileWritable;

    /**
     * @var bool
     */
    private $folderReadable;

    /**
     * @var bool
     */
    private $folderWritable;


    public function __construct()
    {
        $this->fileReadable = function () {
            return true;
        };

        $this->fileReadableByAnonymous = function () {
            return true;
        };

        $this->fileWritable = function () {
            return true;
        };

        $this->folderReadableByAnonymous = function () {
            return true;
        };

        $this->folderReadable = function () {
            return true;
        };

        $this->folderWritable = function () {
            return true;
        };
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
