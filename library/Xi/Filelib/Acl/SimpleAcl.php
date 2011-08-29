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

    /** 
     * {@inheritdoc}
     */
    public function isReadable($resource)
    {
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function isWriteable($resource)
    {
        return true;
    }


    /** 
     * {@inheritdoc}
     */
    public function isReadableByAnonymous($resource)
    {
        return true;
    }





}