<?php

namespace Xi\Filelib\File\Upload;

use \Xi\Filelib\File\FileUpload;

/**
 * Limits file types that are allowed / denied to be uploaded
 *
 * @author pekkis
 *
 */
class Limiter
{

    /**
     * @var array
     */
    private $_accepted = array();

    /**
     * @var array
     */
    private $_denied = array();


    /**
     * Accept a file type. A regex or an array of regexes to accept.
     * 
     * @param mixed $what 
     * @return \Xi\Filelib\File\Uploader
     */
    public function accept($what)
    {
        if(!is_array($what)) {
            $what = array($what);
        }

        foreach($what as $w) {
            $accept = "[" . $w . "]";
            $this->_accepted[] = $accept;

            if(in_array($accept, $this->_denied)) {
                unset($this->_denied[$accept]);
            }

        }

        return $this;

    }


    /**
     * Deny a file type. A regex or an array of regexes to deny.
     * 
     * @param mixed $what 
     * @return \Xi\Filelib\File\Uploader
     */
    public function deny($what)
    {
        if(!is_array($what)) {
            $what = array($what);
        }

        foreach($what as $w) {
            $deny = "[" . $w . "]";
            $this->_denied[] = $deny;
             
            if(in_array($deny, $this->_accepted)) {
                unset($this->_accepted[$deny]);
            }
             
        }

        return $this;

    }

    /**
     * Returns all accepted types
     * 
     * @return array
     */
    public function getAccepted()
    {
        return $this->_accepted;
    }

    /**
     * Returns all denied types
     * 
     * @return array
     */
    public function getDenied()
    {
        return $this->_denied;
    }

    /**
     * Returns whether a file upload may be uploaded
     * 
     * @param \Xi\Filelib\File\FileUpload $upload
     * @return boolean
     */
    public function isAccepted(FileUpload $upload)
    {
        $mimeType = $upload->getMimeType();

        if(!$this->getAccepted() && !$this->getDenied()) {
            return true;
        }

        foreach($this->getDenied() as $denied) {
            if(preg_match($denied, $mimeType)) {
                return false;
            }
        }

        if(!$this->getAccepted()) {
            return true;
        }

        foreach($this->getAccepted() as $accepted) {
            if(preg_match($accepted, $mimeType)) {
                return true;
            }
        }

        return false;

    }


}

