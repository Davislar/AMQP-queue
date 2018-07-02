<?php
/**
 * Created by PhpStorm.
 * User: andrey
 * Date: 02.07.18
 * Time: 10:23
 */

namespace Davislar\AMQP\queue\router;


class RouterController
{
    protected $transport;
    protected $routerConfig;

    /**
     * RouterController constructor.
     * @param $routerConfig
     */
    public function __construct($routerConfig)
    {
        $this->routerConfig = $routerConfig;
        $this->transport = new DataTransport();
    }

    public function getRout($rout){
        return $this->routerConfig[$rout];
    }
    public function getTransportData(){
        return $this->transport->getData();
    }
    public function setTransportData($data){
        return $this->transport->giveData($data);
    }

    public function resetTransportData(){
        $this->transport = new DataTransport();
        return true;
    }
}