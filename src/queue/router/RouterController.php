<?php

namespace Davislar\AMQP\queue\router;


class RouterController
{
    protected $routerConfig;

    /**
     * RouterController constructor.
     * @param $routerConfig
     */
    public function __construct($routerConfig)
    {
        $this->routerConfig = $routerConfig;
    }

    public function getRout($rout){
        return $this->routerConfig[$rout];
    }

}