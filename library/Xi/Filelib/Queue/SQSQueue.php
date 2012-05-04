<?php

namespace Xi\Filelib\Queue;

use Zend_Service_Amazon_Sqs;

class SQSQueue implements Queue
{

    /**
     *
     * @var Zend_Service_Amazon_Sqs
     */
    private $sqs;
    
    private $queueUrl;
    
    public function __construct($key, $secretKey)
    {
        $this->sqs = new Zend_Service_Amazon_Sqs($key, $secretKey);
        $this->queueUrl = $this->sqs->create('test');
    }
    
    
    
    public function enqueue($object)
    {
        $serialized = serialize($object);
        $message_id = $this->sqs->send($this->queueUrl, $serialized);
    }
    
    
    public function dequeue()
    {
        $msg = $this->sqs->receive($this->queueUrl, 1);
        
        $msg = array_shift($msg);
        
        if ($msg) {
            $this->sqs->deleteMessage($this->queueUrl, $msg['handle']) ;
        }
        
        return $msg;
                
    }
    
    
    
    
    
    
}
