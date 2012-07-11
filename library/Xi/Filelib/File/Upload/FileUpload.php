<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\File\Upload;

use Xi\Filelib\File\FileObject;
use DateTime;

/**
 * Uploadable file
 *
 * @author pekkis
 */
class FileUpload
{
    /**
     * @var FileObject
     */
    private $fileObject;

    /**
     * @var string Override file name
     */
    private $overrideFilename;

    /**
     * @var string Override base name
     */
    private $overrideBasename;

    /**
     * @var DateTime
     */
    private $dateUploaded;

    /**
     * @var boolean Temporary file or not
     */
    private $temporary = false;

    /**
     * @param  string     $filename
     * @return FileUpload
     */
    public function __construct($filename)
    {
        $this->fileObject = new FileObject($filename);
    }

    /**
     * Sets override base name
     *
     * @param string $basename
     */
    public function setOverrideBasename($basename)
    {
        $this->overrideBasename = $basename;
    }

    /**
     * Returns override base name
     *
     * @return string
     */
    public function getOverrideBasename()
    {
        return $this->overrideBasename;
    }

    /**
     * Overrides real filename
     *
     * @param string Overriding filename
     */
    public function setOverrideFilename($filename)
    {
        $this->overrideFilename = $filename;
    }

    /**
     * Returns override filename
     *
     * @return string
     */
    public function getOverrideFilename()
    {
        return $this->overrideFilename;
    }

    /**
     * Returns actual upload filename
     *
     * Overrides actual filename with overridden filename. Then overrides base
     * name, if necessary. Returns computed result.
     *
     * @return string
     */
    public function getUploadFilename()
    {
        if (!$uploadName = $this->getOverrideFilename()) {
            $uploadName = $this->fileObject->getFilename();
        }

        if (!$overrideBase = $this->getOverrideBasename()) {
            return $uploadName;
        }

        $pinfo = pathinfo($uploadName);

        $uploadName = $overrideBase;
        if (isset($pinfo['extension']) && $pinfo['extension']) {
            $uploadName .= '.' . $pinfo['extension'];
        }

        return $uploadName;
    }

    /**
     * Returns upload date
     *
     * @return DateTime
     */
    public function getDateUploaded()
    {
        if (!$this->dateUploaded) {
            $this->dateUploaded = new DateTime();
        }

        return $this->dateUploaded;
    }

    /**
     * Sets upload date
     *
     * @param DateTime $dateUploaded
     */
    public function setDateUploaded(DateTime $dateUploaded)
    {
        $this->dateUploaded = $dateUploaded;
    }

    /**
     * Sets whether file is temporary
     *
     * @param bool $temporary
     */
    public function setTemporary($temporary)
    {
        $this->temporary = $temporary;
    }

    /**
     * Returns whether file is temporary
     *
     * @return bool
     */
    public function isTemporary()
    {
        return $this->temporary;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {
        return $this->fileObject->getMimeType();
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return $this->fileObject->getSize();
    }

    /**
     * @return string
     */
    public function getRealPath()
    {
        return $this->fileObject->getRealPath();
    }

    /**
     * Deletes on destruct if temporary
     */
    public function __destruct()
    {
        if ($this->isTemporary()) {
            unlink($this->fileObject->getRealPath());
        }
    }
}
