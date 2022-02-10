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
            array(
                'worker_num' => 2,
//                'daemonize' => true,
                'daemonize' => false,
                'max_request' => 10000,
                'dispatch_mode' => 1
            )
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

    public function onWorkerStart()
    {
        SpringContext::resetConfig();
    }

    public static function start($host = '0.0.0.0', $port = 7999)
    {
        return new static($host, $port);
    }
}
