<?php

namespace Davislar\AMQP\queue\router;


class TransferFacade
{

    public function getData(){
        return DataTransport::getData();
    }

    /**
     * @param $data
     * @return bool
     */
    public function giveData($data){
        DataTransport::giveData($data);
        return true;
    }

    public function resetTransportData(){
        DataTransport::resetTransportData();
        return true;
    }
}