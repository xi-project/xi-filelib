<?php

namespace Xi\Filelib\Acl;

/**
 * Nazi ACL never allows anonymous to read files
 * 
 * @author pekkis
 *
 */
class NaziAcl implements Acl
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
        return false;
    }





}