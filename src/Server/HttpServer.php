<?php

namespace SpringPHP\Server;

use SpringPHP\Core\Crontab;
use SpringPHP\Core\Dispatcher;
use SpringPHP\Core\SpringContext;
use SpringPHP\Inter\TaskInter;
use SpringPHP\Request\RequestHttp;
use SpringPHP\Inter\ServerInter;

//https://www.kancloud.cn/yiyanan/swoole/980197
class HttpServer extends Server implements ServerInter
{
    public $port;
    public $host;

    public function __construct($config = [])
    {
        parent::__construct($config);
        $host = $this->host = $config['host'];
        $port = $this->port = $config['port'];
        $this->serv = $http = new \Swoole\Http\Server($host, $port);
        $http->set(
            [
                'worker_num' => SpringContext::config('settings.worker_num', 2),
                'daemonize' => false,
                'enable_coroutine' => SpringContext::config('settings.enable_coroutine', true),
                'max_request' => SpringContext::config('settings.max_request', 10000),
                'dispatch_mode' => SpringContext::config('settings.dispatch_mode', 1),
                'max_coroutine' => SpringContext::config('settings.max_coroutine', 100000),
                'open_http_protocol' => SpringContext::config('settings.open_http_protocol', true),
                'open_http2_protocol' => SpringContext::config('settings.open_http2_protocol', true),
                'socket_buffer_size' => SpringContext::config('settings.socket_buffer_size', 15 * 1024 * 1024),
                'buffer_output_size' => SpringContext::config('settings.buffer_output_size', 15 * 1024 * 1024),
                'package_max_length' => SpringContext::config('settings.package_max_length', 15 * 1024 * 1024),
                'open_tcp_nodelay' => SpringContext::config('settings.open_tcp_nodelay', true),
                'task_worker_num' => SpringContext::config('settings.task_worker_num', 0),
                'task_enable_coroutine' => SpringContext::config('settings.task_enable_coroutine', false),
                'enable_static_handler' => SpringContext::config('settings.enable_static_handler', false), //是否允许启动静态处理,如果存在会直接发送文件内容给客户端，不再触发onRequest回调
                'document_root' => SpringContext::config('settings.document_root', '')  //静态资源根目录
            ]
        );

        $http->on('workerStart', array($this, 'onWorkerStart'));

        $http->on('start', function ($serv) {
            swoole_set_process_name('spring-php.Manager');
        });

        $http->on('request', function ($request, $response) use ($http, $config) {
            try {
                $result = Dispatcher::init(new RequestHttp($request, $http, $this->swoole_process, $config), $response);
            } catch (\Exception $e) {
                echo var_export($e, true) . PHP_EOL;
            }
            $response->end($result);
        });

        //处理异步任务(此回调函数在task进程中执行)
        $http->on('Task', function (\Swoole\Http\Server $serv, $task_id, $reactor_id, $data) {
            echo "New AsyncTask[id={$task_id}]" . PHP_EOL;
            $obj = is_object($data) ? $data : unserialize($data);
            if (is_object($obj) && $obj instanceof TaskInter) {
                try {
                    $obj->before($task_id);
                    $obj->run($task_id);
                    $obj->after($task_id);
                } catch (\Exception $e) {
                    $obj->onException($e, [
                        'serv' => $serv,
                        'task_id' => $task_id,
                        'reactor_id' => $reactor_id,
                        'data' => $data,
                    ]);
                }
            }
            //返回任务执行的结果
            $serv->finish(serialize($obj));
        });
        //处理异步任务的结果(此回调函数在worker进程中执行)
        $http->on('Finish', function (\Swoole\Http\Server $serv, $task_id, $data) {
            echo "AsyncTask[{$task_id}] Finish start: {$data}" . PHP_EOL;
            $obj = unserialize($data);
            if (is_object($obj) && $obj instanceof TaskInter) {
                try {
                    $obj->finish($task_id);
                } catch (\Exception $e) {
                    $obj->onException($e, [
                        'serv' => $serv,
                        'task_id' => $task_id,
                        'data' => $data,
                    ]);
                }
            }
            $data = serialize($obj);
            echo "AsyncTask[{$task_id}] Finish end: {$data}" . PHP_EOL;
        });
        $this->renderInit($this->port, $config);
        $http->start();
    }

}
