<?php

namespace Davislar\AMQP\tests;


use Davislar\AMQP\messenger\MassageHandler;
use Davislar\AMQP\interfaces\WorkInterface;
use Interop\Amqp\Impl\AmqpMessage;

class TestAction implements WorkInterface
{
    public function execute($message, $transport)
    {
        var_dump($message);
        var_dump($transport);
        var_dump($message->action->data->false);
        if ($message->action->data->false){
            return false;
        }
        return ['data' => 'test'];
    }


}