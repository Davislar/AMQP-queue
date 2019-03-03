<?php

namespace Davislar\AMQP\queue;


use Interop\Queue\Exception;

class Config
{
    /**
     * Configuration of AMQP
     *
     * @var array
     */
    protected static $config;

    /**
     * @param null $key
     * @return mixed
     * @throws \Exception
     */
    public static function getConfig($key = null)
    {
        if (is_null(self::$config)) {
            throw new \Exception('Not set config');
        }
        if (!is_null($key)) {
            return self::$config[$key];
        }
        return self::$config;
    }

    /**
     * @param array $config
     * @return array
     */
    public static function setConfig($config)
    {
        self::validateConfig($config);
        self::$config = $config;
        return self::$config;
    }

    /**
     * @param array $config
     * @return bool
     */
    protected static function validateConfig($config)
    {
        return true;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public static function getQueues()
    {
        $consumers = self::getConfig('consumers');
        $queues = [];
        foreach ($consumers as $consumer) {
            $queues[] = $consumer['queue'];
        }
        return $queues;
    }

    /**
     * @param $queueName
     * @return mixed
     * @throws \Exception
     */
    public static function getQueueConfig($queueName)
    {
        $queues = self::getConfig('queues');

        foreach ($queues as $queue) {
            if ($queue['name'] == $queueName) {
                return $queue;
            }
        }

        throw new \Exception('Not find queue configuration');
    }
}