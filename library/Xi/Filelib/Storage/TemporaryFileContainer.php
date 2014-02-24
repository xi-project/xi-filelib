<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

class TemporaryFileContainer
{
    /**
     * @var string
     */
    private $tempDir;

    /**
     * @var array Registered temporary files
     */
    private $tempFiles = array();

    public function __construct($tempDir = null)
    {
        $this->tempDir = $tempDir ?: sys_get_temp_dir();
    }

    /**
     * Deletes all temp files on destruct
     */
    public function __destruct()
    {
        foreach ($this->tempFiles as $tempFile) {
            unlink($tempFile);
        }
    }

    /**
     * @return string
     */
    public function getTemporaryFilename()
    {
        return tempnam($this->tempDir, 'xi_filelib');
    }

    /**
     * Registers an internal temp file
     *
     * @param string $fo
     */
    public function registerTemporaryFile($file)
    {
        $this->tempFiles[] = $file;
    }
}
