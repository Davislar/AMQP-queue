<?php

namespace Davislar\AMQP\queue\consumer;


use Enqueue\AmqpLib\AmqpConsumer;
use Interop\Amqp\AmqpMessage;
use Interop\Queue\PsrMessage;

class ConsumerFacade
{
    /**
     * @var AmqpConsumer
     */
    protected $consumer;
    protected $queue;

    /**
     * ConsumerFacade constructor.
     * @param $consumer
     */
    public function __construct($consumer)
    {
        $this->consumer = $consumer;
        $this->queue = $this->consumer->getQueue()->getQueueName();
    }

    /**
     * {@inheritdoc}
     */
    public function getConsumerTag()
    {
        return $this->consumer->getConsumerTag();
    }

    /**
     * {@inheritdoc}
     */
    public function clearFlags()
    {
        $this->consumer->clearFlags();
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function addFlag($flag)
    {
        $this->consumer->addFlag($flag);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getFlags()
    {
        return $this->consumer->getFlags();
    }

    /**
     * {@inheritdoc}
     */
    public function setFlags($flags)
    {
        $this->consumer->setFlags($flags);
        return true;
    }

    /**
     * @return \Interop\Amqp\AmqpQueue
     */
    public function getQueue()
    {
        return $this->consumer->getQueue();
    }

    /**
     * @param AmqpMessage $message
     * @param bool $requeue
     * @return bool
     */
    public function reject(AmqpMessage $message, $requeue = false)
    {
        $this->consumer->reject($message, $requeue);
        return true;
    }

    /**
     * @return string
     */
    public function getQueueName()
    {
        return $this->queue;
    }
}