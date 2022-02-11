<?php

namespace SpringPHP\Server;

use SpringPHP\Core\Dispatcher;
use SpringPHP\Core\SpringContext;
use SpringPHP\Request\RequestHttp;
use SpringPHP\Inter\ServerInter;

class HttpServer implements ServerInter
{

    public $http;
    public $get;
    public $post;
    public $header;
    public $server;

    public function __construct($host = '0.0.0.0', $port = 7999)
    {
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
            ]
        );

        $http->on('WorkerStart', array($this, 'onWorkerStart'));

        $http->on('request', function ($request, $response) {
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
                Dispatcher::init(new RequestHttp($request), $response);
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

        $http->start();
    }

    /**
     * 每个worker启动的时候
     */
    public function onWorkerStart()
    {
        Server::onWorkerStart();
    }

    public static function start($host = '0.0.0.0', $port = 7999)
    {
        return new static($host, $port);
    }
}
