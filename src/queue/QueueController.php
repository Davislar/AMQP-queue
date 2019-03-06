<?php

namespace Davislar\AMQP\queue;


use Davislar\AMQP\messenger\MassageHandler;
use Interop\Amqp\Impl\AmqpMessage;

class QueueController
{

    /**
     * Flag for stop process
     * @var bool
     */
    protected $stop;
    /**
     * @var AmqpMessage
     */
    protected $message;

    /**
     * QueueController constructor.
     *
     * @param $config
     */
    public function __construct($config)
    {
        $this->stop = false;
        Config::setConfig($config);
    }

    /**
     * Start initialize AMQP
     *
     * @throws \Exception
     */
    public function init()
    {
        $this->initializeMessengers(Config::getConfig('messengers'));

        try {
            if (Config::getConfig('queues')) {
                $this->initializeAmqp(Config::getConfig());
            }


        } catch (\Exception $exception) {
            MassageHandler::send('start', $exception->getCode(), MassageHandler::VERBOSE_ERROR);
            MassageHandler::send($exception->getMessage(), $exception->getCode(), MassageHandler::VERBOSE_ERROR);
            MassageHandler::send($exception->getFile(), $exception->getCode(), MassageHandler::VERBOSE_ERROR);
            MassageHandler::send($exception->getLine(), $exception->getCode(), MassageHandler::VERBOSE_ERROR);
        }
    }

    /**
     * Start consumers
     *
     * @throws \Interop\Queue\Exception
     */
    public function run()
    {
        try {

            MassageHandler::send('Create consumers ', 0, MassageHandler::VERBOSE_NOTICE);


            Connector::createConsumers();

            MassageHandler::send('Consumers created', 0, MassageHandler::VERBOSE_NOTICE);
        } catch (\Exception $exception) {

            MassageHandler::send('ERROR Code: ', $exception->getCode(), MassageHandler::VERBOSE_ERROR);
            MassageHandler::send($exception->getMessage(), $exception->getCode(), MassageHandler::VERBOSE_ERROR);
            MassageHandler::send($exception->getFile(), $exception->getCode(), MassageHandler::VERBOSE_ERROR);
            MassageHandler::send($exception->getLine(), $exception->getCode(), MassageHandler::VERBOSE_ERROR);
        }

    }

    /**
     * Initialize messengers
     *
     * @param $config
     * @return bool
     * @throws \Exception
     */
    protected function initializeMessengers($config)
    {
        MassageHandler::setMessengers($config);
        MassageHandler::send('Initialize massage handler', 0, MassageHandler::VERBOSE_NOTICE);
        return true;
    }

    /**
     * Initialize Amqp
     *
     * @return bool
     * @throws \Exception
     */
    protected function initializeAmqp($config)
    {
        MassageHandler::send('Start initialize Amqp', 0, MassageHandler::VERBOSE_LOG);
        Connector::initAmqp($config);
        MassageHandler::send('Success initialize Amqp', 0, MassageHandler::VERBOSE_LOG);
        return true;
    }

}