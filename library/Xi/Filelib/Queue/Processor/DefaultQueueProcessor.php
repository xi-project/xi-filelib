<?php

namespace Xi\Filelib\Queue\Processor;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\FileLibrary\File\FileOperator;
use Xi\Filelib\FileLibrary\Folder\FolderOperator;
use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Queue\Message;
use Xi\Filelib\Command;
use ReflectionObject;

class DefaultQueueProcessor implements QueueProcessor
{
    /**
     *
     * @var FileLibrary
     */
    protected $filelib;

    /**
     *
     * @var FileOperator
     */
    private $fileOperator;

    /**
     *
     * @var FolderOperator
     */
    private $folderOperator;

    public function __construct(FileLibrary $filelib)
    {
        $this->filelib = $filelib;
        $this->fileOperator = $filelib->getFileOperator();
        $this->folderOperator = $filelib->getFolderOperator();
        $this->queue = $filelib->getQueue();
    }


    private function injectOperators(Command $command)
    {
        $refl = new ReflectionObject($command);

        if ($refl->hasProperty('fileOperator')) {
            $prop = $refl->getProperty('fileOperator');
            $prop->setAccessible(true);
            $prop->setValue($command, $this->fileOperator);
            $prop->setAccessible(false);
        }

        if ($refl->hasProperty('folderOperator')) {
            $prop = $refl->getProperty('folderOperator');
            $prop->setAccessible(true);
            $prop->setValue($command, $this->folderOperator);
            $prop->setAccessible(false);
        }

    }

    public function process()
    {

        $message = $this->queue->dequeue();
        if (!$message) {
            return false;
        }

        $obj = unserialize($message->getBody());

        // var_dump($obj);

        if ($obj && $obj instanceof Command) {
            $this->injectOperators($obj);

            try {
                $ret = $obj->execute();


                // Queue
                if ($ret instanceof Command) {
                    $this->queue->enqueue(new Message(serialize($ret)));
                }

            } catch (\Exception $e) {

                

                // Errore fatale requenado los messagedos
                return false;
            }

        }

        return true;

    }


}

