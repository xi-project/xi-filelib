<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin;

use Rhumsaa\Uuid\Uuid;
use Xi\Filelib\Event\FileUploadEvent;
use Xi\Filelib\Events;

/**
 * Randomizes all uploads' file names before uploading. Ensures that same file
 * may be uploaded to the same directory time and again
 *
 * @author pekkis
 */
class RandomizeNamePlugin extends AbstractPlugin
{
    protected static $subscribedEvents = array(
        Events::PROFILE_AFTER_ADD => 'onFileProfileAdd',
        Events::FILE_BEFORE_CREATE => 'beforeUpload'
    );


    /**
     * @param string $prefix
     */
    public function __construct($prefix = '')
    {
        $this->prefix = $prefix;
    }

    /**
     * @var string Prefix (for uniqid)
     */
    protected $prefix = '';

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
        $upload->setOverrideBasename($this->prefix . Uuid::uuid4());
        return $upload;
    }
}
