<?php

namespace Xi\Filelib\Acl;

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
     * Returns whether a folderitem or fileitem is readable
     * 
     * @param mixed $resource
     */
    public function isReadable($resource);

    /**
     * Returns whether a folderitem or fileitem is writeable
     * 
     * @param mixed $resource
     */
    public function isWriteable($resource);

    /**
     * Returns whether a folderitem or fileitem is readable by anonymous user
     * 
     * @param mixed $resource
     */
    public function isReadableByAnonymous($resource);


}