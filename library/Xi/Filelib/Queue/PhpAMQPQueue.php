<?php

namespace Xi\Filelib\Queue;

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPMChannel;

class PhpAMQPQueue implements Queue
{
    /**
     *
     * @var AMQPChannel
     */
    private $channel;

    private $exchange;

    private $queue;

    public function __construct($host, $port, $user, $pass, $vhost, $exchange, $queue)
    {
        $conn = new AMQPConnection($host, $port, $user, $pass, $vhost);
        $ch = $conn->channel();

        /*
        name: $queue
        passive: false
        durable: true // the queue will survive server restarts
        exclusive: false // the queue can be accessed in other channels
        auto_delete: false //the queue won't be deleted once the channel is closed.
        */
        $ch->queue_declare($queue, false, true, false, false);

        /*
            name: $exchange
            type: direct
            passive: false
            durable: true // the exchange will survive server restarts
            auto_delete: false //the exchange won't be deleted once the channel is closed.
        */
        $ch->exchange_declare($exchange, 'direct', false, true, false);

        $ch->queue_bind($queue, $exchange, 'filelib');


        $this->exchange = $exchange;
        $this->queue = $queue;
        $this->channel = $ch;


    }

    public function enqueue(Enqueueable $enqueueable)
    {
        $msg = serialize($enqueueable);
        $msg = new AMQPMessage($msg, array('content_type' => 'text/plain', 'delivery-mode' => 1));
        $this->channel->basic_publish($msg, $this->exchange, 'filelib', false, false);
        return $enqueueable->getEnqueueReturnValue();
    }


    public function dequeue()
    {
        $msg = $this->channel->basic_get($this->queue);

        if (!$msg) {
            return null;
        }

        $message = new Message($msg->body);
        $message->setIdentifier($msg->delivery_info['delivery_tag']);
        return $message;

    }


    public function purge()
    {
        return $this->channel->queue_purge($this->queue);
    }


    public function ack(Message $message)
    {
        $this->channel->basic_ack($message->getIdentifier());
    }




}
