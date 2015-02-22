<?php

namespace Xi\Filelib\File;

use Xi\Filelib\Folder\Folder;
use Xi\Collections\Collection\ArrayCollection;
use Xi\Filelib\Backend\Finder\FileFinder;
use Xi\Filelib\FilelibException;

interface FileRepositoryInterface
{
    /**
     * Updates a file
     *
     * @param  File         $file
     * @return FileRepository
     */
    public function update(File $file);

    /**
     * Finds file by id
     *
     * @param  mixed $id File id or array of file ids
     * @return File
     */
    public function find($id);

    /**
     * @return ArrayCollection
     */
    public function findMany($ids);

    /**
     * @param FileFinder $finder
     * @return ArrayCollection
     */
    public function findBy(FileFinder $finder);

    /**
     * @param $uuid
     * @return File
     */
    public function findByUuid($uuid);

    /**
     * Finds file by filename in a folder
     *
     * @param Folder $folder
     * @param $filename
     * @return File
     */
    public function findByFilename(Folder $folder, $filename);

    /**
     * Finds and returns all files
     *
     * @return ArrayCollection
     */
    public function findAll();

    /**
     * Uploads a file
     *
     * @param  mixed            $upload Uploadable, path or object
     * @param  Folder           $folder
     * @return File
     * @throws FilelibException
     */
    public function upload($upload, Folder $folder = null, $profile = 'default');

    public function afterUpload(File $file);

    /**
     * Deletes a file
     *
     * @param File $file
     */
    public function delete(File $file);

    /**
     * Copies a file to folder
     *
     * @param File   $file
     * @param Folder $folder
     */
    public function copy(File $file, Folder $folder);
}