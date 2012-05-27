<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Storage;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\File;
use Xi\Filelib\FilelibException;

/**
 * Filelib Storage interface
 *
 * @author pekkis
 * @todo Something is not perfect yet... Rethink and finalize
 *
 */
interface Storage
{

    /**
     * Sets filelib
     *
     * @return FileLibrary
     */
    public function setFilelib(FileLibrary $filelib);

    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib();

    /**
     * Stores an uploaded file
     *
     * @param File $file
     * @param string $tempFile
     * @throws FilelibException
     */
    public function store(File $file, $tempFile);

    /**
     * Stores a version of a file
     *
     * @param File $file
     * @param string $version
     * @param string $tempFile File to be stored
     * @throws FilelibException
     */
    public function storeVersion(File $file, $version, $tempFile);

    /**
     * Retrieves a file and temporarily stores it somewhere so it can be read.
     *
     * @param File $file
     * @return FileObject
     */
    public function retrieve(File $file);

    /**
     * Retrieves a version of a file and temporarily stores it somewhere so it can be read.
     *
     * @param File $file
     * @param string $version
     * @return FileObject
     */
    public function retrieveVersion(File $file, $version);

    /**
     * Deletes a file
     *
     * @param File $file
     */
    public function delete(File $file);

    /**
     * Deletes a version of a file
     *
     * @param File $file
     * @param $version
     */
    public function deleteVersion(File $file, $version);

}