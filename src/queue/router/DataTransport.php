<?php

namespace Davislar\AMQP\queue\router;


use Davislar\AMQP\exceptions\QueueException;

class DataTransport
{
    /**
     * @var array
     */
    protected static $transportData;
    /**
     * @var QueueException
     */
    protected static $exception;


    public static function getData(){
        return self::$transportData;
    }

    /**
     * @param $data
     * @return bool
     */
    public static function giveData($data){
        if (is_array($data)){
            foreach ($data as $key => $value){
                self::$transportData[$key] = $value;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public static function resetTransportData(){
        self::$transportData = array();
        self::$exception = null;
        return true;
    }

    /**
     * @return mixed
     */
    public static function getException(){
        return self::$exception;
    }

    /**
     * @param $exception
     * @return bool
     */
    public static function setException(QueueException $exception){
        self::$exception = $exception;
        return true;
    }
}