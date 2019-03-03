<?php

namespace Davislar\AMQP\queue;


use Davislar\AMQP\messenger\MassageHandler;
use Davislar\AMQP\queue\consumer\Consumer;
use Davislar\AMQP\queue\consumer\ConsumerFacade;
use Davislar\AMQP\queue\producer\ProducerFacade;
use Enqueue\AmqpLib\AmqpConnectionFactory;
use Enqueue\AmqpLib\AmqpConsumer;
use Interop\Amqp\AmqpDestination;
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
     * @param null $name
     * @return AmqpConsumer
     * @throws \Exception
     */
    public static function getConsumer($name = null)
    {
        if (is_null(self::$consumers)) {
            throw new \Exception('Not set consumer');
        }
        if (!is_null($name)) {
            return self::$consumers[$name];
        }
        return self::$consumers;
    }

    /**
     * @param $consumer
     * @return mixed
     */
    public static function setConsumer($name, $consumer)
    {
        self::$consumers[$name] = $consumer;
    }

    /**
     * @return AmqpContext
     * @throws \Exception
     */
    public static function getConnection()
    {
        if (is_null(self::$psrContext)) {
            throw new \Exception('Not set consumer');
        }
        return self::$psrContext;
    }

    /**
     * @param null $name
     * @return AmqpQueue
     * @throws \Exception
     */
    public static function getQueues($name = null)
    {
        if (is_null(self::$queues)) {
            throw new \Exception('Not set queues');
        }
        if (!is_null($name)) {
            return self::$queues[$name];
        }
        return self::$queues;
    }

    /**
     * @param $psrContext
     * @return bool
     */
    public static function setConnection($psrContext)
    {
        self::$psrContext = $psrContext;
        return true;
    }

    /**
     * @param $consumer
     * @return bool
     * @throws \Exception
     */
    public static function createConnection($consumer)
    {
        $config = Config::getConfig('amqp');
        $factory = new AmqpConnectionFactory($config);
        self::$psrContext = $factory->createContext();
        self::checkQueue($consumer['queue']);
        self::createProducer($consumer['queue']);
        return true;
    }

    /**
     * @param $queue
     */
    protected static function createProducer($queue)
    {
        self::$producer = self::$psrContext->createProducer();
    }

    /**
     * @param $queue
     * @return bool
     * @throws \Exception
     */
    public static function checkQueue($queue)
    {
        $queueConfig = Config::getQueueConfig($queue);
        $fooQueue = self::$psrContext->createQueue($queue);

        if (isset($queueConfig['flags']) && is_array($queueConfig['flags'])) {
            foreach ($queueConfig['flags'] as $flag) {
                $fooQueue->addFlag($flag);
            }
        }

        if (isset($queue['arguments'])) {
            $fooQueue->setArguments($queue['arguments']);
        }

        self::$psrContext->declareQueue($fooQueue);
        self::$queues[$queue] = $fooQueue;

        return true;
    }

    /**
     * @return bool
     * @throws Exception
     */
    public static function createConsumers()
    {
        $consumers = Config::getConfig('consumers');
        foreach ($consumers as $consumer) {
            self::newConsumer($consumer);

            pcntl_signal_dispatch();
        }

        return true;
    }

    /**
     * @param $consumer
     * @return bool
     * @throws Exception
     */
    protected static function newConsumer($consumer)
    {
        if (self::checkOnProcess($consumer)){
            return true;
        }

        MassageHandler::send('Daemon ' . $consumer['name'] . ' not found', 0, MassageHandler::VERBOSE_WARNING);
        if ($consumer['enabled']) {
            self::startDaemonProcess($consumer);
        }
    }

    /**
     * Check on active process of consumer
     *
     * @param $consumer
     * @return bool
     * @throws \Exception
     */
    protected static function checkOnProcess($consumer)
    {
        $pid_file = self::getPidPath($consumer['name']);
        if (file_exists($pid_file)) {

            MassageHandler::send('file_exists ' . $pid_file, 0, MassageHandler::VERBOSE_NOTICE);
            $pid = file_get_contents($pid_file);
            MassageHandler::send('PID: ' . $pid, 0, MassageHandler::VERBOSE_NOTICE);

            if (self::isProcessRunning($pid)) {
                if ($consumer['enabled']) {
                    MassageHandler::send('Daemon ' . $consumer['name'] . ' running and working fine', 0, MassageHandler::VERBOSE_NOTICE);

                    return true;
                } else {
                    self::stopDaemonProcess($consumer, $pid);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Start new consumer process
     *
     * @param $consumer
     * @return bool
     * @throws Exception
     */
    protected static function startDaemonProcess($consumer)
    {
        MassageHandler::send('Try to run daemon ' . $consumer['name'] . '.', 0, MassageHandler::VERBOSE_NOTICE);
        //run daemon
        $pid = pcntl_fork();
        MassageHandler::send('PID: ' . $pid, 0, MassageHandler::VERBOSE_NOTICE);

        if ($pid === -1) {
            MassageHandler::send('pcntl_fork() returned error', 1, MassageHandler::VERBOSE_ERROR);
        } elseif ($pid === 0) {
            self::startConsumer($consumer);
        } else {
            try {
                if (file_put_contents(self::getPidPath($consumer['name']), $pid)) {
                    MassageHandler::send('Daemon ' . $consumer['name'] . ' is running with pid ' . $pid, 0, MassageHandler::VERBOSE_ERROR);
                } else {
                    posix_kill($pid, SIGKILL);
                    pcntl_signal_dispatch();
                }
            } catch (\Exception $exception) {
                MassageHandler::send('ERROR:', 5000, MassageHandler::VERBOSE_ERROR);
                MassageHandler::send($exception->getMessage() . $pid, 5000, MassageHandler::VERBOSE_ERROR);
                posix_kill($pid, SIGKILL);
                pcntl_signal_dispatch();
            }

        }

        return true;
    }

    /**
     * @param $consumer
     * @param $pid
     * @return bool
     */
    protected static function stopDaemonProcess($consumer, $pid)
    {
        MassageHandler::send('Daemon ' . $consumer['name'] . ' running, but disabled in config. Send SIGTERM signal.', 0, MassageHandler::VERBOSE_WARNING);
        if (isset($consumer['hardKill']) && $consumer['hardKill']) {
            posix_kill($pid, SIGKILL);
            pcntl_signal_dispatch();
            MassageHandler::send('Daemon ' . $consumer['name'] . ' was shutdown.', 0, MassageHandler::VERBOSE_WARNING);
        } else {
            posix_kill($pid, SIGTERM);
            pcntl_signal_dispatch();
            MassageHandler::send('Daemon ' . $consumer['name'] . ' was shutdown.', 0, MassageHandler::VERBOSE_WARNING);
        }

        return true;
    }

    /**
     * @param $consumer
     * @throws Exception
     */
    protected static function startConsumer($consumer){
        self::changeProcessName($consumer['name']);
        $pidJob = file_get_contents(self::getPidPath($consumer['name']));
        Connector::createConnection($consumer);
        MassageHandler::send('Connection created for consumer ' . $consumer['name'], 0, MassageHandler::VERBOSE_NOTICE);
        $consumerClass = new Consumer($pidJob, $consumer);
        $consumerClass->runJob();
        MassageHandler::send('Consumer started ' . $consumer['name'], 0, MassageHandler::VERBOSE_NOTICE);
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
     * @throws \Exception
     */
    protected static function getPidPath($name)
    {
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
                MassageHandler::send('Can\'t find cli_set_process_title or setproctitle function', 5000, MassageHandler::VERBOSE_ERROR);
            }
        }
    }

    /**
     * @param AmqpConsumer $consumer
     * @return ConnectorFacade
     */
    public static function getConnectionFacade(AmqpConsumer $consumer)
    {
        return new ConnectorFacade(new ConsumerFacade($consumer), new ProducerFacade());
    }

    /**
     * @param $config
     * @return bool
     */
    public static function initAmqp($config)
    {
        try {
            MassageHandler::send('Create connection', 0, MassageHandler::VERBOSE_NOTICE);

            $factory = self::initFactory($config['amqp']);
            MassageHandler::send('Factory created', 0, MassageHandler::VERBOSE_NOTICE);

            $psrContext = self::initPsrContext($factory);
            MassageHandler::send('PsrContext created', 0, MassageHandler::VERBOSE_NOTICE);

            MassageHandler::send('Create Queues', 0, MassageHandler::VERBOSE_NOTICE);
            $queues = self::initQueues($psrContext, $config['queues']);

            if (isset($config['exchanges'])) {
                MassageHandler::send('Create Exchanges', 0, MassageHandler::VERBOSE_NOTICE);
                self::initExchanges($psrContext, $config['exchanges'], $queues);
            }
        } catch (\Exception $exception) {
            MassageHandler::send('Error' . $exception->getMessage(), 0, MassageHandler::VERBOSE_ERROR);
            exit();
        }

        return true;
    }

    /**
     * @param $config
     * @return AmqpConnectionFactory
     */
    protected static function initFactory($config)
    {
        return new AmqpConnectionFactory($config);
    }

    /**
     * @param AmqpConnectionFactory $factory
     * @return AmqpContext
     */
    protected static function initPsrContext(AmqpConnectionFactory $factory)
    {
        return $factory->createContext();
    }

    /**
     * @param $psrContext
     * @param $queuesConf
     * @return array
     */
    protected static function initQueues($psrContext, $queuesConf)
    {
        $queues = [];
        foreach ($queuesConf as $queue) {
            $fooQueue = $psrContext->createQueue($queue['name']);
            if (isset($queue['flags'])) {
                foreach ($queue['flags'] as $flag) {
                    $fooQueue->addFlag($flag);
                }
            }
            if (isset($queue['arguments'])) {
                $fooQueue->setArguments($queue['arguments']);
            }
            $psrContext->declareQueue($fooQueue);
            $queues[$queue['name']] = $fooQueue;
        }
        return $queues;
    }

    /**
     * @param $psrContext
     * @param $exchangesConf
     * @param $queues
     * @return mixed
     */
    protected static function initExchanges($psrContext, $exchangesConf, $queues)
    {
        foreach ($exchangesConf as $exchange) {
            $fooTopic = $psrContext->createTopic($exchange['name']);
            if (isset($exchange['flags'])) {
                foreach ($exchange['flags'] as $flag) {
                    $fooTopic->addFlag($flag);
                }
            }
            if (isset($exchange['arguments'])) {
                $fooTopic->setArguments($exchange['arguments']);
            }

            $psrContext->declareTopic($fooTopic);
            if (isset($exchange['binds'])) {
                foreach ($exchange['binds'] as $bind) {
                    if (isset($queues[$bind['queue']]) && ($queues[$bind['queue']] instanceof AmqpDestination)) {
                        $psrContext->bind(new \Interop\Amqp\Impl\AmqpBind($fooTopic, $queues[$bind['queue']], (isset($bind['key']) ? $bind['key'] : null)));
                    }
                }
            }
        }
        return $queues;
    }
}