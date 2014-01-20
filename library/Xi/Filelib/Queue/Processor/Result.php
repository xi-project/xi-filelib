<?php

namespace Xi\Filelib\Queue\Processor;

use Xi\Filelib\Queue\Enqueueable;

class Result
{
    private $success;

    private $resultMessage = '';

    private $messages = array();

    public function __construct($success = true, $resultMessage = '')
    {
        $this->success = $success;
    }

    public function addMessage(Enqueueable $enqueueable)
    {
        $this->messages[] = $enqueueable->getMessage();
    }

    public function isSuccess()
    {
        return $this->success;
    }

    public function getResultMessage()
    {
        return $this->resultMessage;
    }

    public function getMessages()
    {
        return $this->messages;
    }

}
