<?php

namespace Xi\Filelib\Folder;

use Xi\Collections\Collection\ArrayCollection;

interface FolderRepositoryInterface
{
    /**
     * Creates a folder
     *
     * @param Folder $folder
     */
    public function create(Folder $folder);

    /**
     * Deletes a folder
     *
     * @param Folder $folder Folder
     */
    public function delete(Folder $folder);

    /**
     * Updates a folder
     *
     * @param Folder $folder Folder
     */
    public function update(Folder $folder);

    /**
     * Finds the root folder
     *
     * @return Folder
     */
    public function findRoot();

    /**
     * Finds a folder
     *
     * @param  mixed  $id Folder id
     * @return Folder
     */
    public function find($id);

    /**
     * @param $url
     * @return Folder
     */
    public function findByUrl($url);

    /**
     * @param $url
     */
    public function createByUrl($url);

    /**
     * Finds subfolders
     *
     * @param  Folder        $folder
     * @return ArrayCollection
     */
    public function findSubFolders(Folder $folder);

    /**
     * Finds parent folder
     *
     * @param  Folder       $folder
     * @return Folder
     */
    public function findParentFolder(Folder $folder);

    /**
     * @param  Folder        $folder Folder
     * @return ArrayCollection
     */
    public function findFiles(Folder $folder);
}