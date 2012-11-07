<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin;

use Xi\Filelib\Event\FileUploadEvent;

/**
 * Randomizes all uploads' file names before uploading. Ensures that same file may be uploaded
 * to the same directory time and again
 *
 * @author pekkis
 *
 */
class RandomizeNamePlugin extends AbstractPlugin
{

    static protected $subscribedEvents = array(
        'fileprofile.add' => 'onFileProfileAdd',
        'file.beforeUpload' => 'beforeUpload'
    );

    /**
     * @var string Prefix (for uniqid)
     */
    protected $prefix = '';

    /**
     * Sets prefix
     *
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * Returns prefix
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    public function beforeUpload(FileUploadEvent $event)
    {
        if (!$this->hasProfile($event->getProfile()->getIdentifier())) {
            return;
        }

        $upload = $event->getFileUpload();

        $pinfo = pathinfo($upload->getUploadFilename());
        $newname = uniqid($this->getPrefix(), true);

        $newname = str_replace('.', '_', $newname);

        if (isset($pinfo['extension'])) {
            $newname .= '.' . $pinfo['extension'];
        }

        $upload->setOverrideFilename($newname);
        return $upload;
    }

}

