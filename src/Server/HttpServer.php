<?php

namespace SpringPHP\Server;

use SpringPHP\Core\Dispatcher;
use SpringPHP\Core\SpringContext;
use SpringPHP\Request\RequestHttp;
use SpringPHP\Inter\ServerInter;
use SpringPHP\Template\Render;

//https://www.kancloud.cn/yiyanan/swoole/980197
class HttpServer implements ServerInter
{

    public $http;
    public $get;
    public $post;
    public $header;
    public $server;
    public $port;
    public $host;

    public function __construct($host = '0.0.0.0', $port = 7999)
    {
        $this->host = $host;
        $this->port = $port;
        $http = new \Swoole\Http\Server($host, $port);
        $http->set(
            [
                'worker_num' => SpringContext::config('settings.worker_num', 2),
                'daemonize' => false,
                'enable_coroutine' => SpringContext::config('settings.enable_coroutine', true),
                'max_request' => SpringContext::config('settings.max_request', 10000),
                'dispatch_mode' => SpringContext::config('settings.dispatch_mode', 1),
                'max_coroutine' => SpringContext::config('settings.max_coroutine', 100000),
                'open_http2_protocol' => SpringContext::config('settings.open_http2_protocol', true),
                'socket_buffer_size' => SpringContext::config('settings.socket_buffer_size', 15 * 1024 * 1024),
                'buffer_output_size' => SpringContext::config('settings.buffer_output_size', 15 * 1024 * 1024),
                'package_max_length' => SpringContext::config('settings.package_max_length', 15 * 1024 * 1024),
                'open_tcp_nodelay' => SpringContext::config('settings.open_tcp_nodelay', true),
                'task_worker_num' => SpringContext::config('settings.task_worker_num', 0),
            ]
        );

        $http->on('workerStart', array($this, 'onWorkerStart'));

        $http->on('start', function ($serv) {
            swoole_set_process_name('spring-php.Manager');
        });

        $http->on('request', function ($request, $response) use ($http) {
            $serv = $http;
            if (isset($request->server)) {
                $this->server = $request->server;
            }
            if (isset($request->header)) {
                $this->header = $request->header;
            }
            if (isset($request->get)) {
                $this->get = $request->get;
            }
            if (isset($request->post)) {
                $this->post = $request->post;
            }

            // TODO handle img

            ob_start();
            try {
                Dispatcher::init(new RequestHttp($request, $serv), $response);
            } catch (\Exception $e) {
                var_dump($e);
            }
            $result = ob_get_contents();
            ob_end_clean();
            // add Header

            // add cookies

            // set status
            $response->end($result);
        });

        //处理异步任务(此回调函数在task进程中执行)
        $http->on('Task', function (\Swoole\Http\Server $serv, $task_id, $reactor_id, $data) {
            echo "New AsyncTask[id={$task_id}]" . PHP_EOL;
            //返回任务执行的结果
            $serv->finish("{$data} -> OK");
        });

//处理异步任务的结果(此回调函数在worker进程中执行)
        $http->on('Finish', function (\Swoole\Http\Server $serv, $task_id, $data) {
            echo "AsyncTask[{$task_id}] Finish: {$data}" . PHP_EOL;
        });
        Render::getInstance()->attachServer($http, $port);

        $http->start();
    }

    /**
     * 每个worker启动的时候
     * @param \Swoole\Http\Server $serv
     */
    public function onWorkerStart(\Swoole\Http\Server $serv, $worker_id)
    {
        Server::onWorkerStart();
        if ($worker_id >= $serv->setting['worker_num']) {
            swoole_set_process_name("spring-php.task.{$worker_id}");
        } else {
            swoole_set_process_name("spring-php.worker.{$worker_id} listen:" . $this->host . ':' . $this->port);
        }
    }

    public static function start($host = '0.0.0.0', $port = 7999)
    {
        return new static($host, $port);
    }
}
