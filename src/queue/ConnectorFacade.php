<?php

namespace Davislar\AMQP\queue;


use Davislar\AMQP\queue\consumer\ConsumerFacade;
use Davislar\AMQP\queue\producer\ProducerFacade;

class ConnectorFacade
{
    /**
     * @var ConsumerFacade
     */
    public $consumer;
    /**
     * @var ProducerFacade
     */
    public $producer;

    /**
     * ConnectorFacade constructor.
     * @param ConsumerFacade $consumer
     * @param ProducerFacade $producer
     */
    public function __construct(ConsumerFacade $consumer, ProducerFacade $producer)
    {
        $this->consumer = $consumer;
        $this->producer = $producer;
    }

    /**
     * @return \Enqueue\AmqpLib\AmqpContext
     * @throws \Exception
     */
    public function getConnection()
    {
        return Connector::getConnection();
    }

}