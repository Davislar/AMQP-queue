<?php

namespace Davislar\AMQP\queue\consumer;


abstract class AbstractConsumerController
{
    protected $transport;
    /**
     * AbstractConsumerController constructor.
     */
    public function __construct()
    {
    }
}