<?php

namespace Davislar\AMQP\queue\consumer;


use Davislar\AMQP\exceptions\QueueException;
use Davislar\AMQP\interfaces\WorkInterface;
use Davislar\AMQP\messenger\MassageHandler;
use Davislar\AMQP\queue\Connector;
use Davislar\AMQP\queue\ConnectorFacade;
use Davislar\AMQP\queue\producer\ConsumerFacade;
use Davislar\AMQP\queue\producer\ProducerFacade;
use Davislar\AMQP\queue\router\DataTransport;
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
     * @var WorkInterface
     */
    protected $action;

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
     */
    public function runJob(){
        while (!$this->stop){
            $message = $this->getMessage();
            MassageHandler::send('Memory use at start', 0, MassageHandler::VERBOSE_LOG);
            MassageHandler::send($this->memoryUsage(true), 0, MassageHandler::VERBOSE_LOG);
            MassageHandler::send('message', 0, MassageHandler::VERBOSE_LOG);
            MassageHandler::send($this->message->getBody(), 0, MassageHandler::VERBOSE_LOG);
            $workers = $this->getWorkers($message);
            foreach ($workers as $worker){
                $this->action = new $worker();
                $this->initTransferData($this->action);
                try{
                    $result = $this->action->execute($message);
                }catch (QueueException $exception){
                    MassageHandler::send($exception->getMessage(), 0, MassageHandler::VERBOSE_ERROR);
                    DataTransport::setException($exception);
                    $result = false;
                }
                if ($result === false){
                    MassageHandler::send('Continue work', 0, MassageHandler::VERBOSE_ERROR);
                    break;
                }
            }
            if ($result !== false){
                $this->consumer->acknowledge($this->message);
            }
            if ($result === false){
                MassageHandler::send('Init on error', 0, MassageHandler::VERBOSE_ERROR);
                $this->onError($this->action);
            }
            $this->resetTransportData($this->action);
            MassageHandler::send('Memory use at finish', 0, MassageHandler::VERBOSE_LOG);
            MassageHandler::send($this->memoryUsage(true), 0, MassageHandler::VERBOSE_LOG);
        }
    }

    /**
     * @return array
     */
    protected function getMessage(){
        $this->message = $this->consumer->receive();
        return json_decode($this->message->getBody());
    }

    /**
     * @param $message
     * @return mixed
     */
    protected function getWorkers($message){
        return $this->router->getRout($message->action->type);
    }

    /**
     * @param $action
     * @return bool
     */
    protected function initTransferData($action){
        if (method_exists($action, 'initTransfer')){
            $action->initTransfer();
            MassageHandler::send('Init transfer data', 0, MassageHandler::VERBOSE_LOG);
        }
        return true;
    }

    /**
     * @param $action
     * @return bool
     */
    protected function resetTransportData($action){
        if (method_exists($action, 'resetTransportData')){
            $action->resetTransportData();
        }else{
            DataTransport::resetTransportData();
        }
        MassageHandler::send('Reset transport data', 0, MassageHandler::VERBOSE_LOG);
        return true;
    }

    /**
     * @param $action
     * @return bool
     * @throws \Interop\Queue\Exception
     */
    protected function onError($action){
        MassageHandler::send('protected function onError', 0, MassageHandler::VERBOSE_LOG);
        if (method_exists($action, 'onError')){
            MassageHandler::send('Init onError method', 0, MassageHandler::VERBOSE_LOG);
            $action->onError(Connector::getConnectionFacade($this->consumer), $this->message, DataTransport::getException());
        }else{
            Connector::$producer->send($this->consumer->getQueue()->getQueueName(), $this->getMessage());
            MassageHandler::send('message', 0, MassageHandler::VERBOSE_LOG);
            $this->consumer->reject($this->message);
        }
        MassageHandler::send('Reset transport data', 0, MassageHandler::VERBOSE_LOG);
        return true;
    }

    /**
     * @param bool $real_usage
     * @return int
     */
    protected function memoryUsage($real_usage = false){
        return memory_get_usage($real_usage);
    }

}