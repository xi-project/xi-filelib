<?php

namespace Xi\Filelib\Linker;

use \Xi\Filelib\FileLibrary,
    \Xi\Filelib\File\File,
    \Xi\Filelib\Plugin\VersionProvider\VersionProvider
    ;

/**
 * Linker interface
 * 
 * @author pekkis
 *
 */
interface Linker
{

    /**
     * Sets filelib
     * 
     * @return Linker
     */
    public function setFilelib(FileLibrary $filelib);
    
    /**
     * Returns filelib
     *
     * @return FileLibrary
     */
    public function getFilelib();

    /**
     * Returns link for a version of a file
     *
     * @param File $file
     * @param VersionProvider $version Version plugin
     * @return string Versioned link
     */
    public function getLinkVersion(File $file, VersionProvider $version);

    /**
     * Returns a link for a file
     *
     * @param File $file
     * @return string Link
     */
    public function getLink(File $file);
}
