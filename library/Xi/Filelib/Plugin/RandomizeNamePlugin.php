<?php

namespace Xi\Filelib\Plugin;

/**
 * Randomizes all uploads' file names before uploading. Ensures that same file may be uploaded
 * to the same directory time and again
 *
 * @package Xi_Filelib
 * @author pekkis
 *
 */
class RandomizeNamePlugin extends \Xi\Filelib\Plugin\AbstractPlugin
{

    /**
     * @var string Prefix (for uniqid)
     */
    protected $_prefix = '';

    /**
     * Sets prefix
     *
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->_prefix = $prefix;
    }

    /**
     * Returns prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->_prefix;
    }

    public function beforeUpload(\Xi\Filelib\File\Upload\FileUpload $upload)
    {
        $pinfo = pathinfo($upload->getOverrideFilename());
        $newname = uniqid($this->getPrefix(), false);

        if(isset($pinfo['extension'])) {
            $newname .= '.' . $pinfo['extension'];
        }

        $upload->setOverrideFilename($newname);
        return $upload;
    }

}



