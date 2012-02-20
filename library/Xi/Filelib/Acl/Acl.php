<?php

namespace Xi\Filelib\Acl;

use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;

/**
 * Interface for building an ACL implementation for Filelib
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
interface Acl
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