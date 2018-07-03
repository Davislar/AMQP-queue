<?php

namespace Davislar\AMQP\tests;


use Davislar\AMQP\messenger\MassageHandler;
use Davislar\AMQP\interfaces\WorkInterface;
use Davislar\AMQP\queue\traits\Transfer;
use Interop\Amqp\Impl\AmqpMessage;

class Test2Action implements WorkInterface
{
    use Transfer;

    public function execute($message)
    {
        var_dump('TestAction');
        var_dump($this->transfer->getData());
        var_dump($message);
        return true;
    }


}