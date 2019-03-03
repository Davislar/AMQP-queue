<?php

namespace Davislar\AMQP\queue\traits;


use Davislar\AMQP\queue\router\TransferFacade;

trait Transfer
{
    /**
     * @var TransferFacade
     */
    protected $transfer;

    public function initTransfer()
    {
        $this->transfer = new TransferFacade();
        return true;
    }

    /**
     * @return bool
     */
    public function resetTransportData()
    {
        $this->transfer->resetTransportData();
        return true;
    }
}