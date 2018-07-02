<?php

namespace Davislar\AMQP\queue\router;


class DataTransport
{
    protected $transportData;

    /**
     * @param $data
     * @return bool
     */
    public function giveData($data){
        if (is_array($data)){
            foreach ($data as $key => $value){
                $this->transportData[$key] = $value;
            }
        }
        return true;
    }

    public function getData(){
        return $this->transportData;
    }
}