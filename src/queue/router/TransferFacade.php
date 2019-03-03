<?php

namespace Davislar\AMQP\queue\router;


class TransferFacade
{

    /**
     * @return array
     */
    public function getData()
    {
        return DataTransport::getData();
    }

    /**
     * @param $data
     * @return bool
     */
    public function giveData($data)
    {
        DataTransport::giveData($data);
        return true;
    }

    /**
     * @return bool
     */
    public function resetTransportData()
    {
        DataTransport::resetTransportData();
        return true;
    }
}