<?php

namespace Davislar\AMQP\queue\producer;


use Davislar\AMQP\queue\Connector;
use Enqueue\AmqpLib\AmqpProducer;
use Interop\Queue\PsrMessage;

class ProducerController
{
    /**
     * @var AmqpProducer
     */
    protected $producer;

    /**
     * ProducerController constructor.
     * @param $producer
     */
    public function __construct($producer)
    {
        $this->producer = $producer;
    }

    /**
     * @param $queue
     * @param $message
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\InvalidDestinationException
     * @throws \Interop\Queue\InvalidMessageException
     */
    public function send($queue, $message){
        var_dump($message);
        if ($message instanceof \stdClass){
            var_dump(json_encode($message));
            $message = Connector::getConnection()->createMessage(json_encode($message));
        }
        if ($message instanceof PsrMessage){
            $this->producer->send($queue, $message);
        }
    }
}