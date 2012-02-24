<?php

namespace Xi\Filelib\Plugin\VersionProvider;

use Xi\Filelib\File\File;
use Xi\Filelib\Plugin\Plugin;

/**
 * Interface for version providing plugins
 *
 * @author pekkis
 *
 */
interface VersionProvider extends Plugin
{
    /**
     * Sets file extension
     *
     * @param string $extension File extension
     */
    public function setExtension($extension);

    /**
     * Returns the plugins file extension
     *
     * @return string
     */
    public function getExtension();
    
    /**
     * Sets file types for this version plugin.
     *
     * @param array $providesFor Array of file types
     */
    public function setProvidesFor(array $providesFor);

    /**
     * Returns file types which the version plugin provides version for.
     *
     * @return array
     */
    public function getProvidesFor();

    /**
     * Returns whether the plugin provides a version for a file.
     *
     * @param File $file File item
     * @return boolean
     */
    public function providesFor(File $file);

    /**
     * Sets version identifier
     *
     * @param string $identifier Unique identifier for this version
     */
    public function setIdentifier($identifier);

    /**
     * Returns version identifier
     *
     * @return string
     */
    public function getIdentifier();

    
}
