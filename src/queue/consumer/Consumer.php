<?php

namespace Davislar\AMQP\queue\consumer;


use Davislar\AMQP\messenger\MassageHandler;
use Davislar\AMQP\queue\Connector;
use Davislar\AMQP\queue\router\RouterController;
use Enqueue\AmqpLib\AmqpConsumer;
use Interop\Amqp\Impl\AmqpMessage;

class Consumer extends AbstractConsumerController
{
    protected $pidJob;
    /**
     * @var AmqpConsumer
     */
    protected $consumer;
    protected $stop;
    /**
     * @var AmqpMessage
     */
    protected $message;
    protected $router;

    /**
     * Consumer constructor.
     * @param $pidJob
     * @param $consumer
     * @throws \Interop\Queue\Exception
     */
    public function __construct($pidJob, $consumer)
    {
        $this->pidJob = $pidJob;
        $this->router = new RouterController($consumer['router']);
        $fooQueue = Connector::getQueues($consumer['queue']);
        $this->consumer = Connector::getConnection()->createConsumer($fooQueue);

    }

    /**
     * @throws \Interop\Queue\Exception
     * @throws \Interop\Queue\InvalidDestinationException
     * @throws \Interop\Queue\InvalidMessageException
     */
    public function runJob(){
//        var_dump($consumer);
        while (!$this->stop){
            $this->message = $this->consumer->receive();
            $message = json_decode($this->message->getBody());
            MassageHandler::send('message', 0, MassageHandler::VERBOSE_LOG);
            MassageHandler::send($this->message->getBody(), 0, MassageHandler::VERBOSE_LOG);
            $workers = $this->router->getRout($message->action->type);
            $this->router->resetTransportData();
            foreach ($workers as $worker){
                $action = new $worker();
                $result = $action->execute($message, $this->router->getTransportData());
                if ($result === false){
                    break;
                }
                $this->router->setTransportData($result);
            }
            var_dump($result);
            if ($result !== false){
                $this->consumer->acknowledge($this->message);
            }
            if ($result === false){
                Connector::$producers->send($this->consumer->getQueue(), $message);
                MassageHandler::send('message', 0, MassageHandler::VERBOSE_LOG);
                $this->consumer->reject($this->message);
            }
            sleep(3);
        }
    }


}