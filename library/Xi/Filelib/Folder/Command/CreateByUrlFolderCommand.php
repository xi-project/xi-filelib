<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Folder\Command;

use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderRepository;

class CreateByUrlFolderCommand extends BaseFolderCommand
{

    /**
     *
     * @var string
     */
    private $url;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function execute()
    {
        $folder = $this->folderRepository->findByUrl($this->url);
        if ($folder) {
            return $folder;
        }

        $rootFolder = $this->folderRepository->findRoot();

        $exploded = explode('/', $this->url);

        $folderNames = array();

        $created = null;
        $previous = null;

        $count = 0;

        while (sizeof($exploded) || !$created) {

            $folderNames[] = $folderCurrent = array_shift($exploded);
            $folderName = implode('/', $folderNames);
            $created = $this->folderRepository->findByUrl($folderName);

            if (!$created) {
                $created = Folder::create(
                    array(
                        'parent_id' => $previous ? $previous->getId() : $rootFolder->getId(),
                        'name' => $folderCurrent,
                    )
                );

                $this->folderRepository->createCommand(
                    FolderRepository::COMMAND_CREATE,
                    array($created)
                )->execute();
            }
            $previous = $created;
        }

        return $created;
    }

    public function getTopic()
    {
        return 'xi_filelib.command.folder.create_by_url';
    }
}
