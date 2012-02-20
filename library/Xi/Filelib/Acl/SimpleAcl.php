<?php

namespace Xi\Filelib\Acl;

use Xi\Filelib\File\File;
use Xi\Filelib\Folder\Folder;

/**
 * Simple ACL allows everything to everyone
 * 
 * @author pekkis
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
    public function isFileReadable(File $file)
    {
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function isFileWritable(File $file)
    {
        return true;
    }


    /** 
     * {@inheritdoc}
     */
    public function isFileReadableByAnonymous(File $file)
    {
        return $this->isReadableByAnonymous;
    }


    /** 
     * {@inheritdoc}
     */
    public function isFolderReadable(Folder $file)
    {
        return true;
    }


    /**
     * {@inheritdoc}
     */
    public function isFolderWritable(Folder $file)
    {
        return true;
    }


    /** 
     * {@inheritdoc}
     */
    public function isFolderReadableByAnonymous(Folder $file)
    {
        return $this->isReadableByAnonymous;
    }




}