<?php

namespace Xi\Filelib\Queue\Processor;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\FileLibrary\File\FileOperator;
use Xi\Filelib\FileLibrary\Folder\FolderOperator;
use Xi\Filelib\Queue\Queue;
use Xi\Filelib\Queue\Message;
use Xi\Filelib\Command;
use ReflectionObject;

class DefaultQueueProcessor extends AbstractQueueProcessor
{

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

