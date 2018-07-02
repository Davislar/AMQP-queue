<?php

namespace Davislar\AMQP\tests;


use Davislar\AMQP\messenger\MassageHandler;
use Davislar\AMQP\interfaces\WorkInterface;
use Interop\Amqp\Impl\AmqpMessage;

class Test2Action implements WorkInterface
{
    public function execute($message, $transport)
    {
        var_dump($message);
        var_dump($transport);
        return true;
    }


}