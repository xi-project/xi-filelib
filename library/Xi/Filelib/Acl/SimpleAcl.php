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

    private $isReadableByAnonymous = true;
    
    
    public function __construct($isReadableByAnonymous = true)
    {
        $this->isReadableByAnonymous = $isReadableByAnonymous;
    }
    
    
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
        return $this->isReadableByAnonymous;
    }





}