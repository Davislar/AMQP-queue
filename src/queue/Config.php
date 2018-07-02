<?php

namespace Davislar\AMQP\queue;


use Interop\Queue\Exception;

class Config
{
    protected static $config;

    /**
     * @param string|null $key
     * @return mixed
     * @throws Exception
     */
    public static function getConfig(string $key = null){
        if (is_null(self::$config)){
            throw new Exception('Not set config');
        }
        if (!is_null($key)){
            return self::$config[$key];
        }
        return self::$config;
    }

    /**
     * @param array $config
     * @return array
     */
    public static function setConfig(array $config){
        self::validateConfig($config);
        self::$config = $config;
        return self::$config;
    }

    protected static function validateConfig(array $config){
        return true;
    }

    public static function getQueues(){
        $consumers = self::getConfig('consumers');
        $queues = [];
        foreach ($consumers as $consumer){
            $queues[] = $consumer['queue'];
        }
        return $queues;
    }
}