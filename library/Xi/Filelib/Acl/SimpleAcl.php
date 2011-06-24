<?php

namespace Xi\Filelib\Acl;

/**
 * Simple ACL allows everything to everyone
 * 
 * @author pekkis
 * @package Xi_Filelib
 *
 */
class SimpleAcl implements Acl
{

    public function isReadable($resource)
    {
        return true;
    }


    public function isWriteable($resource)
    {
        return true;
    }


    public function isReadableByAnonymous($resource)
    {
        return true;
    }





}