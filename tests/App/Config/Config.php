<?php



use SpringPHP\Server\Server;
use SpringPHP\Core\SpringContext;
use SpringPHP\Template\Render;

return [
    "error_page" => [\App\Controller\Error::class, 'index404'],
    'servers' => [
        [
            'module_name' => 'Test',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => SpringContext::config('local.servers.0.port', 7999), //7999
            'template' => [ //视图渲染配置
                'socket_type' => Render::SOCKET_UNIX,
                'open' => true,
                'callback' => function ($tpl) {
                    $smarty = new \App\Template\Smarty();
                    return $smarty->render($tpl['template'], $tpl['data'], $tpl['options']);
                },
                'count' => 3,
                'asynchronous' => false, //smarty 模板这边必须设置为false,因为smarty协程不安全
            ],
            'crontab' => [
                'open' => true,
                'list' => [
                    [
                        'class' => \App\Timer\FirstTimer::class,
                        'ms' => 100
                    ],
                    [
                        'class' => \App\Timer\SecondTimer::class,
                        'ms' => 1000
                    ],
                ]
            ]
        ],
        [
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 8098,
            'template' => [ //视图渲染配置
                'socket_type' => Render::SOCKET_TCP,
                'open' => true,
                'callback' => function ($tpl) {
                    $smarty = new \App\Template\Smarty();
                    return $smarty->render($tpl['template'], $tpl['data'], $tpl['options']);
                }
            ],
            'crontab' => [
                'open' => false,
                'list' => [
                    [
                        'class' => \App\Timer\FirstTimer::class,
                        'ms' => 30000
                    ],
                    [
                        'class' => \App\Timer\SecondTimer::class,
                        'ms' => 50000
                    ],
                ]
            ]
        ],
        [
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 8297,
            'template' => [ //视图渲染配置
                'socket_type' => Render::SOCKET_TCP,
                'open' => true,
                'callback' => function ($tpl) {
                    $smarty = new \App\Template\Smarty();
                    return $smarty->render($tpl['template'], $tpl['data'], $tpl['options']);
                }
            ],
            'crontab' => [
                'open' => false,
                'list' => [
                    [
                        'class' => \App\Timer\FirstTimer::class,
                        'ms' => 30000
                    ],
                    [
                        'class' => \App\Timer\SecondTimer::class,
                        'ms' => 50000
                    ],
                ]
            ]
        ],
        [
            'module_name' => 'Manager',  //管理模块，类似服务注册中心
            'type' => Server::SERVER_SOCKET,
            'host' => '0.0.0.0',
            'port' => 8498,
            'crontab' => [
                'open' => true,
                'list' => [
                    [
                        'class' => \App\Timer\ManagerTimer::class,
                        'ms' => 60000
                    ],
                ],
                'after_time' => 10000
            ]
        ],
        [
            'module_name' => 'WebSocket',
            'type' => Server::SERVER_WEBSOCKET,
            'host' => '0.0.0.0',
            'port' => 8397,
            'event_worker_start' => function (\Swoole\Server $serv, $worker_id) {
                \App\Event\WebSocketWorkerStartEvent::start();
            },
            'event_open' => function(\Swoole\Server $ws, \Swoole\Http\Request $request){
                \App\Event\WebSocketOpenEvent::start($ws,$request);
            },
            'event_close' => function(\Swoole\Server $ws, $fd){
                \App\Event\WebSocketCloseEvent::start($ws, $fd);
            },
            'settings' => [
                'worker_num' => 1,
                'task_worker_num' => 0,
            ]
        ],
        [
            'module_name' => 'WebSocket',
            'type' => Server::SERVER_WEBSOCKET,
            'host' => '0.0.0.0',
            'open' => true,
            'port' => 8398,
            'event_worker_start' => function (\Swoole\Server $serv, $worker_id) {
                \App\Event\WebSocketWorkerStartEvent::start();
            },
            'event_open' => function(\Swoole\Server $ws, \Swoole\Http\Request $request){
                \App\Event\WebSocketOpenEvent::start($ws,$request);
            },
            'event_close' => function(\Swoole\Server $ws, $fd){
                \App\Event\WebSocketCloseEvent::start($ws, $fd);
            },
            'settings' => [
                'worker_num' => 1,
                'task_worker_num' => 0,
            ]
        ],
        [
            'module_name' => 'WebSocket',
            'type' => Server::SERVER_WEBSOCKET,
            'host' => '0.0.0.0',
            'port' => 8399,
            'event_worker_start' => function (\Swoole\Server $serv, $worker_id) {
                \App\Event\WebSocketWorkerStartEvent::start();
            },
            'event_open' => function(\Swoole\Server $ws, \Swoole\Http\Request $request){
                \App\Event\WebSocketOpenEvent::start($ws,$request);
            },
            'event_close' => function(\Swoole\Server $ws, $fd){
                \App\Event\WebSocketCloseEvent::start($ws, $fd);
            },
            'settings' => [
                'worker_num' => 1,
                'task_worker_num' => 0,
            ]
        ],
        [
            'module_name' => 'WebSocket',
            'type' => Server::SERVER_SOCKET,
            'host' => '0.0.0.0',
            'port' => 8497,
        ],


    ],
    'settings' => [
        'enable_coroutine' => true,
        'worker_num' => 2,
        'runtime_path' => SPRINGPHP_ROOT . '/runtime',
        'pid_file' => SPRINGPHP_ROOT . '/runtime/spring-php.pid',
        'open_tcp_nodelay' => true,
        'max_coroutine' => 100000,
        'open_http2_protocol' => true,
        'max_request' => 100000,
        'socket_buffer_size' => 15 * 1024 * 1024,
        'buffer_output_size' => 15 * 1024 * 1024,
        'package_max_length' => 15 * 1024 * 1024,
        // Task Worker 数量，根据您的服务器配置而配置适当的数量
        'task_worker_num' => 2,
        // 因为 `Task` 主要处理无法协程化的方法，所以这里推荐设为 `false`，避免协程下出现数据混淆的情况
        'task_enable_coroutine' => false,
        'document_root' => SPRINGPHP_ROOT . '/static',
        'enable_static_handler' => true,
        'debug' => false
    ],
];