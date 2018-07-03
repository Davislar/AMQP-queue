<?php

namespace Davislar\AMQP\queue;


use Davislar\AMQP\messenger\MassageHandler;
use Davislar\AMQP\queue\consumer\Consumer;
use Davislar\AMQP\queue\consumer\ConsumerFacade;
use Davislar\AMQP\queue\producer\ProducerFacade;
use Enqueue\AmqpLib\AmqpConnectionFactory;
use Enqueue\AmqpLib\AmqpConsumer;
use Interop\Amqp\AmqpQueue;
use Interop\Queue\Exception;
use Enqueue\AmqpLib\AmqpContext;

class Connector
{
    /**
     * @var AmqpContext
     */
    protected static $psrContext;
    /**
     * @var AmqpQueue
     */
    protected static $queues;

    /**
     * @var AmqpConsumer
     */
    protected static $consumers;
    /**
     * @var ProducerFacade
     */
    public static $producer;

    /**
     * @return mixed
     * @throws Exception
     */
    public static function getConsumer($name = null){
        if (is_null(self::$consumers)){
            throw new Exception('Not set consumer');
        }
        if (!is_null($name)){
            var_dump($name);
            return self::$consumers[$name];
        }
        return self::$consumers;
    }

    /**
     * @param $consumer
     * @return mixed
     */
    public static function setConsumer($name, $consumer){
        self::$consumers[$name] = $consumer;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function getConnection(){
        if (is_null(self::$psrContext)){
            throw new Exception('Not set consumer');
        }
        return self::$psrContext;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public static function getQueues($name = null){
//        var_dump('name');
//        var_dump($name);
//        var_dump(self::$queues[$name]);
        if (is_null(self::$queues)){
            throw new Exception('Not set queues');
        }
        if (!is_null($name)){
            return self::$queues[$name];
        }
        return self::$queues;
    }

    /**
     * @param $psrContext
     * @return bool
     */
    public static function setConnection($psrContext){
        self::$psrContext = $psrContext;
        return true;
    }

    public static function createConnection($consumer){
        $config = Config::getConfig('amqp');
        $factory = new AmqpConnectionFactory($config);
        self::$psrContext = $factory->createContext();
        self::checkQueue($consumer['queue']);
        self::createProducer($consumer['queue']);
        return true;
    }
    protected static function createProducer($queue){
        self::$producer = self::$psrContext->createProducer();
    }
    public static function checkQeueus(){
        $queues = Config::getQueues();
        foreach ($queues as $queue){
            $fooQueue = self::$psrContext->createQueue($queue);
            $fooQueue->addFlag(AmqpQueue::FLAG_DURABLE);
            self::$psrContext->declareQueue($fooQueue);
            self::$queues[$queue] = $fooQueue;
        }
        var_dump($queues);
        return true;
    }

    public static function checkQueue($queue){
        $queues = Config::getQueues();
            $fooQueue = self::$psrContext->createQueue($queue);
            $fooQueue->addFlag(AmqpQueue::FLAG_DURABLE);
            self::$psrContext->declareQueue($fooQueue);
            self::$queues[$queue] = $fooQueue;
        return true;
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public static function createConsumer(){
        $consumers = Config::getConfig('consumers');
        foreach ($consumers as $consumer){
            $parentPID = getmypid();
            pcntl_signal_dispatch();
            self::newConsumer($consumer);
            pcntl_signal_dispatch();
            self::changeProcessName($consumer['name']);
//            self::$consumers[$consumer['name']] = self::$psrContext->createConsumer(self::$queues[$consumer['queue']]);
        }
//        var_dump(self::$consumers);
        return true;
    }
    protected static function newConsumer($consumer){
        $pid_file = self::getPidPath($consumer['name']);
        if (file_exists($pid_file)) {
            MassageHandler::send('file_exists ' . $pid_file, 0, MassageHandler::VERBOSE_NOTICE);
            $pid = file_get_contents($pid_file);
            MassageHandler::send('$pid: ' . $pid, 0, MassageHandler::VERBOSE_NOTICE);
            if (self::isProcessRunning($pid)) {
                if ($consumer['enabled']) {
                    MassageHandler::send('Daemon ' . $consumer['name'] . ' running and working fine', 0, MassageHandler::VERBOSE_NOTICE);
                    return true;
                } else {
                    MassageHandler::send('Daemon ' . $consumer['name'] . ' running, but disabled in config. Send SIGTERM signal.', 0, MassageHandler::VERBOSE_WARNING);
                    if (isset($consumer['hardKill']) && $consumer['hardKill']) {
                        posix_kill($pid, SIGKILL);
                    } else {
                        posix_kill($pid, SIGTERM);
                    }

                    return true;
                }
            }
        }
        MassageHandler::send('Daemon ' . $consumer['name'] . ' not found', 0, MassageHandler::VERBOSE_WARNING);
        if ($consumer['enabled']) {
            MassageHandler::send('Try to run daemon ' . $consumer['name'] . '.', 0,  MassageHandler::VERBOSE_NOTICE);
            $command_name = $consumer['name'] . DIRECTORY_SEPARATOR . 'index';
            //run daemon
            $pid = pcntl_fork();
            MassageHandler::send('$pid: ' . $pid, 0,  MassageHandler::VERBOSE_NOTICE);

            if ($pid === -1) {
                MassageHandler::send('pcntl_fork() returned error', 1,  MassageHandler::VERBOSE_ERROR);
            } elseif ($pid === 0) {
//                $this->cleanLog();
                $pidJob = file_get_contents(self::getPidPath($consumer['name']));
                Connector::createConnection($consumer);
                MassageHandler::send('Connection created', 0, MassageHandler::VERBOSE_NOTICE);
                $consumerClass = new Consumer($pidJob, $consumer);
                $consumerClass->runJob();
                MassageHandler::send('Start consumer' . $consumer['name'], 0,  MassageHandler::VERBOSE_NOTICE);
            } else {
//                $this->initLogger();
                try{
                    if (file_put_contents(self::getPidPath($consumer['name']), $pid)) {
                        MassageHandler::send('Daemon ' . $consumer['name'] . ' is running with pid ' . $pid, 0,  MassageHandler::VERBOSE_ERROR);
                    }else{
                        posix_kill($pid, SIGKILL);
                    }
                }catch (\Exception $exception){
                    MassageHandler::send('newConsumer', 5000,  MassageHandler::VERBOSE_ERROR);
                    MassageHandler::send($exception->getMessage() . $pid, 5000,  MassageHandler::VERBOSE_ERROR);
                    posix_kill($pid, SIGKILL);
                }

            }
        }
    }
    /**
     * @param $pid
     *
     * @return bool
     */
    protected static function isProcessRunning($pid)
    {
        return file_exists("/proc/$pid");
    }

    /**
     * @param $name
     * @return string
     * @throws Exception
     */
    protected static function getPidPath($name){
        $pidDir = Config::getConfig('pidDir');
        if (!file_exists($pidDir)) {
            mkdir($pidDir, 0744, true);
        }
        return $pidDir . DIRECTORY_SEPARATOR . $name;
    }

    /**
     * @param $name
     */
    protected static function changeProcessName($name)
    {
        //rename process
        if (version_compare(PHP_VERSION, '5.5.0') >= 0) {
            cli_set_process_title($name);
        } else {
            if (function_exists('setproctitle')) {
                setproctitle($name);
            } else {
                MassageHandler::send('Can\'t find cli_set_process_title or setproctitle function', 5000,  MassageHandler::VERBOSE_ERROR);
            }
        }
    }

    public static function getConnectionFacade(AmqpConsumer $consumer){
        return new ConnectorFacade(new ConsumerFacade($consumer), new ProducerFacade());
    }
}