<?php

namespace Xi\Filelib\Queue;

use Pekkis\Queue\Processor\MessageHandler;
use Pekkis\Queue\Message;
use Pekkis\Queue\Processor\Result;
use Xi\Filelib\Attacher;
use Xi\Filelib\File\Command\AfterUploadFileCommand;
use Xi\Filelib\File\Command\CopyFileCommand;
use Xi\Filelib\File\Command\DeleteFileCommand;
use Xi\Filelib\File\Command\UpdateFileCommand;
use Xi\Filelib\File\Command\UploadFileCommand;
use Xi\Filelib\File\Upload\FileUpload;
use Xi\Filelib\FileLibrary;
use Xi\Filelib\File\File;
use Xi\Filelib\File\FileOperator;
use Xi\Filelib\Folder\Command\CreateByUrlFolderCommand;
use Xi\Filelib\Folder\Command\CreateFolderCommand;
use Xi\Filelib\Folder\Command\DeleteFolderCommand;
use Xi\Filelib\Folder\Command\UpdateFolderCommand;
use Xi\Filelib\Folder\Folder;
use Xi\Filelib\Folder\FolderOperator;
use Xi\Filelib\RuntimeException;
use Xi\Filelib\File\Resource;

class FilelibMessageHandler implements MessageHandler, Attacher
{
    private $handledMessages = array(
        'xi_filelib.command.file.after_upload',
        'xi_filelib.command.file.copy',
        'xi_filelib.command.file.delete',
        'xi_filelib.command.file.update',
        'xi_filelib.command.file.upload',
        'xi_filelib.command.folder.create_by_url',
        'xi_filelib.command.folder.create',
        'xi_filelib.command.folder.delete',
        'xi_filelib.command.folder.update',
    );

    /**
     * @var FileLibrary
     */
    private $filelib;

    /**
     * @var FileOperator
     */
    private $fiop;

    /**
     * @var FolderOperator
     */
    private $foop;

    public function attachTo(FileLibrary $filelib)
    {
        $this->filelib = $filelib;

        $this->fiop = $filelib->getFileOperator();
        $this->foop = $filelib->getFolderOperator();
    }

    public function willHandle(Message $message)
    {
        return in_array($message->getType(), $this->handledMessages);
    }

    public function handle(Message $message)
    {

        try {

            switch ($message->getType()) {

                case 'xi_filelib.command.file.after_upload':
                    $command = $this->createAfterUploadFileCommand($message);
                    break;

                case 'xi_filelib.command.file.copy':
                    $command = $this->createCopyFileCommand($message);
                    break;

                case 'xi_filelib.command.file.update':
                    $command = $this->createUpdateFileCommand($message);
                    break;

                case 'xi_filelib.command.file.delete':
                    $command = $this->createDeleteFileCommand($message);
                    break;

                case 'xi_filelib.command.file.upload':
                    $command = $this->createUploadFileCommand($message);
                    break;

                case 'xi_filelib.command.folder.create_by_url':
                    $command = $this->createCreateByUrlFolderCommand($message);
                    break;

                case 'xi_filelib.command.folder.create':
                    $command = $this->createCreateFolderCommand($message);
                    break;

                case 'xi_filelib.command.folder.update':
                    $command = $this->createUpdateFolderCommand($message);
                    break;

                case 'xi_filelib.command.folder.delete':
                    $command = $this->createDeleteFolderCommand($message);
                    break;
            }

            $command->execute();
            return new Result(true);

        } catch (\Exception $e) {
            return new Result(false, $e->getMessage());
        }
    }

    /**
     * @param Message $message
     * @return AfterUploadFileCommand
     */
    private function createAfterUploadFileCommand(Message $message)
    {
        $data = $message->getData();

        $command = $this->fiop->createCommand(
            'Xi\Filelib\File\Command\AfterUploadFileCommand',
            array(
                $this->fiop->find($data['file_id']),
            )
        );
        return $command;
    }

    /**
     * @param Message $message
     * @return DeleteFileCommand
     */
    private function createDeleteFileCommand(Message $message)
    {
        $data = $message->getData();

        $command = $this->fiop->createCommand(
            'Xi\Filelib\File\Command\DeleteFileCommand',
            array(
                $this->fiop->find($data['file_id']),
            )
        );
        return $command;
    }

    /**
     * @param Message $message
     * @return CopyFileCommand
     */
    private function createCopyFileCommand(Message $message)
    {
        $data = $message->getData();

        $command = $this->fiop->createCommand(
            'Xi\Filelib\File\Command\CopyFileCommand',
            array(
                $this->fiop->find($data['file_id']),
                $this->foop->find($data['folder_id']),
                $message->getUuid()
            )
        );
        return $command;
    }

    /**
     * @param Message $message
     * @return UpdateFileCommand
     */
    private function createUpdateFileCommand(Message $message)
    {
        $data = $message->getData();

        $file = File::create($data['file_data']);
        $file->setResource(Resource::create(array('id' => $data['file_data']['resource_id'])));

        $command = $this->fiop->createCommand(
            'Xi\Filelib\File\Command\UpdateFileCommand',
            array(
                $file,
            )
        );
        return $command;
    }

    /**
     * @param Message $message
     * @return UploadFileCommand
     */
    private function createUploadFileCommand(Message $message)
    {
        $data = $message->getData();

        $upload = new FileUpload($data['upload']['realPath']);
        $upload->setOverrideBasename($data['upload']['overrideBasename']);
        $upload->setOverrideFilename($data['upload']['overrideFilename']);
        $upload->setTemporary($data['upload']['temporary']);

        $command = $this->fiop->createCommand(
            'Xi\Filelib\File\Command\UploadFileCommand',
            array(
                $upload,
                $this->foop->find($data['folder_id']),
                $data['profile'],
                $message->getUuid()
            )
        );
        return $command;
    }

    /**
     * @param Message $message
     * @return DeleteFolderCommand
     */
    private function createDeleteFolderCommand(Message $message)
    {
        $data = $message->getData();

        $command = $this->foop->createCommand(
            'Xi\Filelib\Folder\Command\DeleteFolderCommand',
            array(
                $this->foop->find($data['folder_id']),
            )
        );
        return $command;
    }

    /**
     * @param Message $message
     * @return UpdateFolderCommand
     */
    private function createUpdateFolderCommand(Message $message)
    {
        $data = $message->getData();

        $folder = Folder::create($data['folder_data']);

        $command = $this->foop->createCommand(
            'Xi\Filelib\Folder\Command\UpdateFolderCommand',
            array(
                $folder,
            )
        );
        return $command;
    }

    /**
     * @param Message $message
     * @return CreateByUrlFolderCommand
     */
    private function createCreateByUrlFolderCommand(Message $message)
    {
        $data = $message->getData();

        $command = $this->foop->createCommand(
            'Xi\Filelib\Folder\Command\CreateByUrlFolderCommand',
            array(
                $data['url'],
            )
        );
        return $command;
    }

    /**
     * @param Message $message
     * @return CreateFolderCommand
     */
    private function createCreateFolderCommand(Message $message)
    {
        $data = $message->getData();

        $folder = Folder::create($data['folder_data']);

        $command = $this->foop->createCommand(
            'Xi\Filelib\Folder\Command\CreateFolderCommand',
            array(
                $folder,
            )
        );
        return $command;
    }
}
