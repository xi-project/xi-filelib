<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Plugin;

use Ramsey\Uuid\Uuid;
use Xi\Filelib\Event\FileEvent;
use Xi\Filelib\Events;

/**
 * Randomizes all uploads' file names before uploading. Ensures that same file
 * may be uploaded to the same directory time and again
 *
 * @author pekkis
 */
class RandomizeNamePlugin extends BasePlugin
{
    protected static $subscribedEvents = array(
        Events::PROFILE_AFTER_ADD => 'onFileProfileAdd',
        Events::FILE_BEFORE_CREATE => 'beforeCreate'
    );

    public function beforeCreate(FileEvent $event)
    {
        $file = $event->getFile();

        if (!$this->belongsToProfile($file->getProfile())) {
            return;
        }

        $file->getData()->set(
            'plugin.randomize_name.original_name',
            $file->getName()
        );


        $pinfo = pathinfo($file->getName());
        $newName = Uuid::uuid4()->toString();
        if (isset($pinfo['extension']) && $pinfo['extension']) {
            $newName .= '.' . $pinfo['extension'];
        }
        $file->setName($newName);
    }
}
