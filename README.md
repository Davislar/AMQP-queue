# AMQP-queue
AMQP queue


###Config

         [
              'pidDir' => '/var/www/Projects/test/AMQP-queue/runtime',
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
          ]