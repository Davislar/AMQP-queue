<?php

namespace Davislar\AMQP\queue;


use Davislar\AMQP\messenger\MassageHandler;
use Interop\Amqp\Impl\AmqpMessage;

class QueueController
{

    protected $router;
    protected $stop;
    /**
     * @var AmqpMessage
     */
    protected $message;

    public function __construct($config)
    {
        $this->stop = false;
        Config::setConfig($config);
        MassageHandler::send('Set Config', 0, \Davislar\AMQP\messenger\MassageHandler::VERBOSE_NOTICE);
//        var_dump(Config::getConfig());
    }

    /**
     * @throws \Interop\Queue\Exception
     */
    public function start(){
        $this->initializeMassagers(Config::getConfig('messengers'));
        MassageHandler::send('Initialize massage handler', 0, \Davislar\AMQP\messenger\MassageHandler::VERBOSE_NOTICE);
        try{
            if (Config::getConfig('queues')){
                $this->initializeAmqp(Config::getConfig());
            }
            Connector::createConsumer();
            MassageHandler::send('Consumer created', 0, MassageHandler::VERBOSE_NOTICE);
//            $this->run();
        }catch (\Exception $exception){
            MassageHandler::send('start', $exception->getCode(), MassageHandler::VERBOSE_ERROR);
            MassageHandler::send($exception->getMessage(), $exception->getCode(), MassageHandler::VERBOSE_ERROR);
            MassageHandler::send($exception->getFile(), $exception->getCode(), MassageHandler::VERBOSE_ERROR);
            MassageHandler::send($exception->getLine(), $exception->getCode(), MassageHandler::VERBOSE_ERROR);
        }
    }

    /**
     *
     * @return bool
     * @throws \Exception
     */
    protected function initializeMassagers($config){
        MassageHandler::setMessengers($config);
        return true;
    }

    /**
     *
     * @return bool
     * @throws \Exception
     */
    protected function initializeAmqp($config){
        Connector::initAmqp($config);
        return true;
    }

    /**
     * @throws \Interop\Queue\Exception
     */
    protected function run(){
        $consumer = Connector::getConsumer('test');
        while (!$this->stop){
            $this->message = $consumer->receive();

            MassageHandler::send('message', 0, MassageHandler::VERBOSE_LOG);
            MassageHandler::send($this->message->getBody(), 0, MassageHandler::VERBOSE_LOG);
            $consumer->acknowledge($this->message);
        }
    }

    /**
     * Set new process name
     */
    protected function changeProcessName($name)
    {
        //rename process
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            cli_set_process_title($this->config->name);
        } else {
            if (function_exists('setproctitle')) {
                setproctitle($this->config->name);
            } else {
                ConsoleHelper::consolePrint(5000, 'Can\'t find cli_set_process_title or setproctitle function', ConsoleHelper::BG_RED);
            }
        }
    }

}