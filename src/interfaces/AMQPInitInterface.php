<?php

namespace Davislar\AMQP\interfaces;


interface AMQPInitInterface
{
    /**
     * Flags
     */
    const
        FLAG_NOPARAM = 0,
        FLAG_JUST_CONSUME = 1,
        FLAG_DURABLE = 2,
        FLAG_PASSIVE = 4,
        FLAG_EXCLUSIVE = 8,
        FLAG_AUTODELETE = 16,
        FLAG_INTERNAL = 32,
        FLAG_NOLOCAL = 64,
        FLAG_AUTOACK = 128,
        FLAG_IFEMPTY = 256,
        FLAG_IFUNUSED = 512,
        FLAG_MANDATORY = 1024,
        FLAG_IMMEDIATE = 2048,
        FLAG_MULTIPLE = 4096,
        FLAG_NOWAIT = 8192,
        FLAG_REQUEUE = 16384;

    /**
     * Types
     */
    const
        TYPE_DIRECT = 'direct',
        TYPE_FANOUT = 'fanout',
        TYPE_TOPIC = 'topic',
        TYPE_HEADERS = 'headers';

    const
        PHP_AMQP_MAX_CHANNELS = 256;

    const
        ARGUMENT_MESSAGE_TTL = 'x-message-ttl',
        ARGUMENT_EXPIRES = 'x-expires',
        ARGUMENT_MAX_LENGTH = 'x-max-length',
        ARGUMENT_MAX_LENGTH_BYTES = 'x-max-length-bytes',
        ARGUMENT_DEAD_LETTER_EXCHANGE = 'x-dead-letter-exchange',
        ARGUMENT_DEAD_LETTER_ROUTE_KEY = 'x-dead-letter-routing-key',
        ARGUMENT_MAX_PRIORITY = 'x-max-priority';

    public function init($config);

    public function initQueues($config);

    public function initExchanges($config);
}