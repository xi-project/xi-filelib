<?php

/**
 * This file is part of the Xi Filelib package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Filelib\Queue;

use AMQPConnection;
use AMQPChannel;
use AMQPExchange;
use AMQPQueue;
use AMQPEnvelope;

class PeclAMQPQueue implements Queue
{
    private $conn;

    /**
     *
     * @var AMQPChannel
     */
    private $channel;

    private $exchange;

    private $queue;

    public function __construct($host, $port, $login, $password, $vhost, $exchangeName, $queueName)
    {
        $conn = new AMQPConnection(array(
            'host' => $host,
            'port' => $port,
            'vhost' => $vhost,
            'login' => $login,
            'password' => $password
        ));

        $conn->connect();

        $channel = new AMQPChannel($conn);

        $exchange = new AMQPExchange($channel);
        $exchange->setName($exchangeName);
        $exchange->setType(AMQP_EX_TYPE_DIRECT);
        $exchange->setFlags(AMQP_DURABLE);
        $exchange->declare();

        $queue = new AMQPQueue($channel);
        $queue->setName($queueName);
        $queue->setFlags(AMQP_DURABLE);
        $queue->declare();
        $queue->bind($exchangeName, 'filelib');

        $this->conn = $conn;
        $this->exchange = $exchange;
        $this->channel = $channel;
        $this->queue = $queue;

    }

    public function enqueue(Enqueueable $enqueueable)
    {
        $msg = serialize($enqueueable);
        $this->exchange->publish($msg, 'filelib');
    }

    public function dequeue()
    {
        $msg = $this->queue->get();

        if (!$msg) {
            return null;
        }

        $message = new Message($msg->getBody());
        $message->setIdentifier($msg->getDeliveryTag());

        return $message;
    }


    public function purge()
    {
        return $this->queue->purge();
    }


    public function ack(Message $message)
    {
        $this->queue->ack($message->getIdentifier());
    }



    public function __destruct()
    {

        $this->conn->disconnect();
    }



}
