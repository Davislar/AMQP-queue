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

    /**
     * @param $rout
     * @return array
     */
    public function getRout($rout)
    {
        return isset($this->routerConfig[$rout]) ? $this->routerConfig[$rout] : [];
    }

}