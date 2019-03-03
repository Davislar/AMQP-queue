<?php

namespace Davislar\AMQP\interfaces;


interface WorkInterface
{
    public function execute($message);
}