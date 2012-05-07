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
    
    private $queueName;
    
    public function __construct($key, $secretKey, $endPoint, $queueName = 'filelib')
    {
        $this->queueName = $queueName;
        $this->sqs = new Zend_Service_Amazon_Sqs($key, $secretKey, $endPoint);
        $this->queueUrl = $this->sqs->create($this->queueName);
    }
    
            
    
    public function enqueue($object)
    {
        $serialized = serialize($object);
        $message_id = $this->sqs->send($this->queueUrl, $serialized);
    }
    
    
    public function dequeue()
    {
        $msg = $this->sqs->receive($this->queueUrl, 1);

        if (!$msg) {
            return null;
        }

        $msg = array_shift($msg);
        $this->sqs->deleteMessage($this->queueUrl, $msg['handle']) ;
        return unserialize($msg['body']);
                
    }
    
    
    public function purge()
    {
        $this->sqs->delete($this->queueUrl);
        $this->queueUrl = $this->sqs->create($this->queueName);
    }
    
    
    
    
}
