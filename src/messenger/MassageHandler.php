<?php

namespace Davislar\AMQP\messenger;


use Davislar\AMQP\interfaces\MessengerInterface;

class MassageHandler
{
    const
        VERBOSE_LOG = 'LOG',
        VERBOSE_ERROR = 'ERROR',
        VERBOSE_DEBUG = 'DEBUG',
        VERBOSE_NOTICE = 'NOTICE',
        VERBOSE_WARNING = 'WARNING';

    /**
     * @var array
     */
    static $messengers;

    /**
     * @param array $messengers
     * @throws \Exception
     */
    public static function setMessengers(array $messengers = [])
    {
        if (is_array($messengers)){
            foreach ($messengers as $messenger) {
                if (!is_array($messenger) || !isset($messenger['class']) || !isset($messenger['config'])) {
                    throw new \Exception('Not valid config MassageHandler');
                }
                $messengerObj = new $messenger['class']($messenger['config']);
                self::addMessenger($messengerObj);
            }
        }
    }

    /**
     * @param MessengerInterface $messenger
     */
    protected static function addMessenger(MessengerInterface $messenger)
    {
        self::$messengers[] = $messenger;
    }

    /**
     * @param $msg
     * @param $code
     * @param $level
     */
    public static function send($msg, $code, $level)
    {
        foreach (self::$messengers as $messenger) {
            if ($messenger->verbose($level)) {
                $messenger->send($msg, $code, $level);
            }
        }
    }
}