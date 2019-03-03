<?php

namespace Davislar\AMQP\interfaces;


interface MessengerInterface
{
    public function __construct($config);

    public function verbose($level);

    public function send($msg, $code, $level);
}