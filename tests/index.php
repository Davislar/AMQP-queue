<?php

defined('APP_DEV_ENV') or define('APP_DEV_ENV', true);
defined('APP_DEM_ENV') or define('APP_DEM_ENV', false);

use Davislar\AMQP\messenger\MassageHandler;
use Davislar\AMQP\queue\Config;
use Davislar\AMQP\queue\Connector;
use Davislar\AMQP\messenger\ConsoleHandler;
use Davislar\AMQP\queue\QueueController;

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using

/**
 * Task data
 * {"toroute":"manager","action":{"type":"delSteamFriend","data":{"userSteamId":"76561198384936205","false":true}}}
 */

$app = new QueueController([
    'pidDir' => '/var/www/Projects/test/AMQP-queue/runtime',
    'amqp' => [
        'host' => 'rmq.dzensteam.com',
        'port' => 5672,
        'vhost' => '/test',
        'user' => 'admin',
        'pass' => 'time',
        'persisted' => false,
        'connection_timeout' => 10000,
    ],
    'consumers' => [
        [
            'name' => 'test',
            'enabled' => true,
            'queue' => 'test',
            'router' => [
                'delSteamFriend' => [
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

$app->start();




