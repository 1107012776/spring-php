<?php

namespace SpringPHP\Core;

use SpringPHP\Request\RequestHttp;

class Dispatcher
{
    protected $queryString = '';
    protected $routing = '';
    protected $controller = '';
    protected $action = '';
    protected $request;
    /**
     * @var \Swoole\Http\Response
     */
    protected $response;

    protected function __construct()
    {

    }

    public static function createDispatcher()
    {
        return new static();
    }


    /**
     * 初始化
     * @param $request
     * @param $response
     */
    public static function init($request, $response)
    {
        $dis = static::createDispatcher();
        $dis->request = $request;
        $dis->response = $response;
        if ($request instanceof RequestHttp) {
            $dis->http($request);
        }
        $dis->bootstrap();
    }

    protected function http(RequestHttp $request)
    {
        $uri = $request->getUri();
        $arr = explode('?', $uri);
        $this->routing = $arr[0];
        $this->queryString = isset($arr[1]) ? $arr[1] : '';
    }

    public function bootstrap()
    {
        $arr = explode('/', $this->routing);
        $controller = $this->controller = !empty($arr[1]) ? $arr[1] : 'Index';
        $action = $this->action = !empty($arr[2]) ? $arr[2] : 'index';
        $fix = '\App\Controller\\';
        if (!empty($controller) && !empty($action)) {
            $controllerClass = $fix . $controller;
            class_exists($controllerClass) && $obj = new $controllerClass($this->request, $this->response);
            if (empty($obj) || !method_exists($obj, $action)) {
                $this->response->setStatusCode(404);
                $errorPageArr = SpringContext::$app->getConfig('error_page');
                if (class_exists($errorPageArr[0])) {
                    $controllerClass = $errorPageArr[0];
                    $obj = new $controllerClass($this->request, $this->response);
                    $action = $errorPageArr[1];
                } else {
                    echo '404';
                    return;
                }
            }
            $response = $obj->$action();
            if (is_string($response)) {
                echo $response;
            }
        }
    }
}