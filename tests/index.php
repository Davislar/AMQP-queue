<?php

defined('APP_DEV_ENV') or define('APP_DEV_ENV', true);
defined('APP_DEM_ENV') or define('APP_DEM_ENV', false);

use Davislar\AMQP\messenger\MassageHandler;
use Davislar\AMQP\queue\Config;
use Davislar\AMQP\queue\Connector;
use Davislar\AMQP\messenger\ConsoleHandler;
use Davislar\AMQP\queue\QueueController;
use Davislar\AMQP\interfaces\AMQPInitInterface;
use Enqueue\AmqpLib\AmqpConnectionFactory;

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using

/**
 * Task data
 * {"toroute":"manager","action":{"type":"delSteamFriend","data":{"userSteamId":"76561198384936205","false":true}}}
 */

$app = new QueueController([
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
]);


$app->init();
$app->run();




