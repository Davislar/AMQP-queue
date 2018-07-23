<?php

namespace Davislar\AMQP\tests;


use Davislar\AMQP\exceptions\QueueException;
use Davislar\AMQP\interfaces\WorkInterface;
use Davislar\AMQP\queue\ConnectorFacade;
use Davislar\AMQP\queue\traits\Transfer;
use Interop\Queue\PsrMessage;

class TestAction implements WorkInterface
{
    use Transfer;

    /**
     * @param $message
     * @return bool
     * @throws QueueException
     */
    public function execute($message)
    {
        if ($message->action->data->false){
            throw new QueueException('throw QueueException', 0);
        }
        return true;
    }

    /**
     * @param ConnectorFacade $connection
     * @param $message
     * @param QueueException $exception
     * @return bool
     * @throws \Interop\Queue\Exception
     */
    public function onError(ConnectorFacade $connection,PsrMessage $message, QueueException $exception){
        $connection->consumer->reject($message);
        $connection->producer->send($connection->consumer->getQueueName(), json_decode($message->getBody()));
        return true;
    }


}