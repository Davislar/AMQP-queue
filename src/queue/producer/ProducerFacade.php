<?php

namespace Davislar\AMQP\queue\producer;


use Davislar\AMQP\queue\Connector;
use Enqueue\AmqpLib\AmqpProducer;
use Interop\Queue\PsrMessage;

class ProducerFacade
{

    /**
     * @param $queue
     * @param $message
     * @return bool
     * @throws \Interop\Queue\Exception
     */
    public function send($queue, $message)
    {
        if ($message instanceof \stdClass) {
            $message = Connector::getConnection()->createMessage(json_encode($message));
        }
        if ($message instanceof PsrMessage) {
            Connector::$producer->send($this->getQueueByName($queue), $message);
        }
        return true;
    }

    /**
     * @param $queue
     * @return \Interop\Amqp\AmqpQueue
     * @throws \Exception
     */
    public function getQueueByName($queue)
    {
        return Connector::getQueues($queue);
    }
}