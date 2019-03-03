# AMQP-queue
AMQP queue

### Install

    composer require davislar/amqp


### Config

         [
             'pidDir' => '/var/www/AMQP-queue/runtime',
             'amqp' => [
                 'host' => 'localhost',
                 'port' => 5670,
                 'vhost' => '/',
                 'user' => 'guest',
                 'pass' => 'guest',
                 'persisted' => false,
                 'connection_timeout' => 10000,
             ],
             'queues' => [
                 [
                     'name' => '111',
                     'flags' => [
                         AMQPInitInterface::FLAG_DURABLE
                     ],
                 ],
                 [
                     'name' => '222',
         
                 ]
             ],
             'exchanges' => [
                 [
                     'name' => '111',
                     'type' => AMQPInitInterface::TYPE_DIRECT,
                     'flags' => [
                         AMQPInitInterface::FLAG_DURABLE
                     ],
                     'arguments' => [
                         AMQPInitInterface::ARGUMENT_MAX_LENGTH => 20000
                     ],
                     'binds' => [
                         [
                             'queue' => '111'
                         ]
                     ]
                 ],
                 [
                     'name' => '1111',
                     'binds' => [
                         [
                             'queue' => '222',
                             'key' => '1'
                         ]
                     ]
                 ]
             ],
             'consumers' => [
                 [
                     'name' => 'testName',
                     'enabled' => true,
                     'queue' => '111',
                     'router' => [
                         'manager' => [
                             \Davislar\AMQP\tests\TestAction::class,
                             \Davislar\AMQP\tests\Test2Action::class
                         ]
                     ]
                 ]
             ],
             'messengers' => [
                 [
                     'class' => ConsoleHandler::class,
                     'config' => [
                         'levels' => [
                             MassageHandler::VERBOSE_NOTICE,
                             MassageHandler::VERBOSE_LOG,
                             MassageHandler::VERBOSE_ERROR,
                             MassageHandler::VERBOSE_WARNING
                         ]
                     ]
                 ]
             ]
         ]