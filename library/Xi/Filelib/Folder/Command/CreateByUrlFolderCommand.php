<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\Folder\Folder;

class CreateByUrlFolderCommand extends AbstractFolderCommand
{

    /**
     *
     * @var string
     */
    private $url;

    public function __construct(FolderOperator $folderOperator, $url)
    {
        parent::__construct($folderOperator);
        $this->url = $url;
    }

    public function execute()
    {
        $folder = $this->folderOperator->findByUrl($this->url);
        if ($folder) {
            return $folder;
        }

        $rootFolder = $this->folderOperator->findRoot();

        $exploded = explode('/', $this->url);

        $folderNames = array();

        $created = null;
        $previous = null;

        $count = 0;

        while (sizeof($exploded) || !$created) {

            $folderNames[] = $folderCurrent = array_shift($exploded);
            $folderName = implode('/', $folderNames);
            $created = $this->folderOperator->findByUrl($folderName);

            if (!$created) {
                $created = $this->folderOperator->getInstance(array(
                    'parent_id' => $previous ? $previous->getId() : $rootFolder->getId(),
                    'name' => $folderCurrent,
                ));

                $command = $this->folderOperator->createCommand('Xi\Filelib\Folder\Command\CreateFolderCommand', array($this->folderOperator, $created));
                $command->execute();

            }
            $previous = $created;
        }

        return $created;
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        $this->url = $data['url'];
        $this->uuid = $data['uuid'];
    }

    public function serialize()
    {
        return serialize(array(
            'url' => $this->url,
            'uuid' => $this->uuid,
        ));
    }

}
