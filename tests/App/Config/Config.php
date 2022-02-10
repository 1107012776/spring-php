<?php
use SpringPHP\Server\Server;
use \SpringPHP\Core\SpringContext;

return [
    "error_page" => ['\App\Controller\Error', 'index404'],
    'mode' => SWOOLE_PROCESS,
    'servers' => [
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' =>  SpringContext::config('local.servers.0.port'), //7999
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
            ],
        ],
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 7998,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
            ],
        ],
        [
            'name' => 'http',
            'type' => Server::SERVER_HTTP,
            'host' => '0.0.0.0',
            'port' => 7997,
            'sock_type' => SWOOLE_SOCK_TCP,
            'callbacks' => [
            ],
        ],
    ],
    'settings' => [
        'daemonize' => 1,  //为1的时候为守护模式
        'enable_coroutine' => true,
        'worker_num' => swoole_cpu_num(),
        'runtime_path' => SPRINGPHP_ROOT . '/runtime',
        'pid_file' => SPRINGPHP_ROOT . '/runtime/spring-php.pid',
        'open_tcp_nodelay' => true,
        'max_coroutine' => 100000,
        'open_http2_protocol' => true,
        'max_request' => 100000,
        'socket_buffer_size' => 15 * 1024 * 1024,
        'buffer_output_size' => 15 * 1024 * 1024,
        'package_max_length' => 15 * 1024 *1024,
        // Task Worker 数量，根据您的服务器配置而配置适当的数量
        'task_worker_num' => 0,
        // 因为 `Task` 主要处理无法协程化的方法，所以这里推荐设为 `false`，避免协程下出现数据混淆的情况
        'task_enable_coroutine' => false,
    ],
];