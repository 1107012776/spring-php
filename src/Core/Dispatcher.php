<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Core;

use SpringPHP\Component\SimpleAutoload;
use SpringPHP\Inter\RequestInter;
use SpringPHP\Request\RequestHttp;
use SpringPHP\Request\RequestWebSocket;
use SpringPHP\Request\RequestSocket;
use SpringPHP\Response\SocketResponse;
use SpringPHP\Route\Router;
use SpringPHP\Session\Session;
use Swoole\Http\Response;

class Dispatcher
{
    protected $queryString = '';
    protected $routing = '';
    protected $controller = '';
    protected $action = '';
    protected $request;
    protected $module_name = '';
    /**
     * @var \SpringPHP\Inter\ResponseInter
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
     * @return string
     */
    public static function init($request, $response)
    {
        $dis = static::createDispatcher();
        $dis->request = $request;
        $dis->response = $response;
        if ($request instanceof RequestHttp) {
            $dis->parse($request);
            return $dis->bootstrap();
        }
        if ($request instanceof RequestWebSocket) {
            $dis->parse($request);
            return $dis->bootstrap();
        }
        if ($request instanceof RequestSocket) {
            $dis->parse($request);
            return $dis->bootstrap();
        }
        return '';
    }


    protected function parse(RequestInter $request)
    {
        $uri = $request->getUri();
        $arr = explode('?', $uri);
        /**
         * @var Router $router
         */
        $router = SpringContext::config('router');
        if (!empty($router)) {
            if ($resRoute = $router->match($arr[0], $request->method())) {
                $params = $resRoute->getParams();
                $request->setParams($params);
                $this->routing = $resRoute->getStorage();
            }
        }
        if (empty($this->routing)) {
            $this->routing = $arr[0];
        }
        $config = $request->getConfig();
        $this->module_name = empty($config['module_name']) ? '' : $config['module_name'];
        $this->queryString = isset($arr[1]) ? $arr[1] : '';
        if (!empty($this->queryString) && $this->request instanceof RequestSocket) {
            parse_str($this->queryString, $getData);
            $request = $this->request;
            /**
             * @var RequestSocket $request
             */
            is_array($getData) && $request->setMergeData($getData);
        }
    }


    public function bootstrap()
    {
        $response = $this->response;
        if (!empty($response) && get_class($response) == Response::class) {
            /**
             * @var Response $response
             */
            $response->setHeader('Content-Type', 'text/html;charset=UTF-8');
        }
        if (is_callable($this->routing)) {
            $routingCallBack = $this->routing;
            return $routingCallBack($this->request, $response);
        }
        $arr = explode('/', $this->routing);
        $controller = $this->controller = !empty($arr[1]) ? $arr[1] : 'Index';
        $action = $this->action = !empty($arr[2]) ? $arr[2] : 'index';
        $fix = '\App\Controller\\';
        if (!empty($this->module_name)) {
            SimpleAutoload::add([
                'App\\' . $this->module_name => SPRINGPHP_ROOT . '/App/Modules/' . $this->module_name
            ]);
            $fix = 'App\\' . $this->module_name . '\\Controller\\';
        }
        if (empty($controller) || empty($action)) {
            return '';
        }
        $controllerClass = $fix . $controller;
        class_exists($controllerClass) && $obj = new $controllerClass();
        if (empty($obj) || !method_exists($obj, $action)) {   //找不到具体的页面
            return $this->errorPage($response, $action);
        }
        $reflection = new \ReflectionMethod($controllerClass, $action);
        if (!$reflection->isPublic()
            || $reflection->isConstructor()
            || $reflection->isDestructor()
        ) {  //利用反射进行校验
            return $this->errorPage($response, $action);
        }
        /**
         * @var Controller $obj
         */
        $obj->init($this->request, $response);
        if (!$obj->beforeAction($action)) {
            if (!empty($response) && get_class($response) == Response::class) {
                $response->setStatusCode(403);
            } elseif (!empty($response) && $response instanceof SocketResponse) {
                $socketResponse = $response;
                /**
                 * @var SocketResponse $socketResponse
                 */
                $result = $socketResponse->response(['code' => 403]);
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }
            return '';
        }
        $result = $obj->$action();
        $this->responseSession($response);
        if (is_string($result)) {
            return $result;
        }
        if (is_null($result)) {
            return '';
        }
        if (!is_array($result)) {
            if (is_object($result)) {
                return (array)$result;
            }
            return var_export($result, true);
        }
        if (!empty($response) && $response instanceof SocketResponse) {
            $socketResponse = $response;
            /**
             * @var SocketResponse $socketResponse
             */
            $result = $socketResponse->response($result);
        }
        if (!empty($response) && get_class($response) == Response::class) {
            $response->setHeader('Content-Type', 'application/json;charset=UTF-8');
        }
        return json_encode($result, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 404页面
     * @param $response
     * @param $action
     * @return false|string
     */
    protected function errorPage($response, $action)
    {
        $responseSocketFunc = function ($socketResponse, $code = 0) {
            /**
             * @var SocketResponse $socketResponse
             */
            $socketResponse->setRequest($this->request);
            $result = $socketResponse->response(['code' => $code]);
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        };
        if (!empty($response) && get_class($response) == Response::class) {
            /**
             * @var Response $response
             */
            $response->setStatusCode(404);
        } elseif (!empty($response) && $response instanceof SocketResponse) {
            return $responseSocketFunc($response, 404);
        }
        $errorPageArr = SpringContext::$app->getConfig('error_page');
        if (!empty($errorPageArr[0]) && class_exists($errorPageArr[0])) {
            $controllerClass = $errorPageArr[0];
            /**
             * @var Controller $obj
             */
            $obj = new $controllerClass();
            $obj->init($this->request, $response);
            if (!$obj->beforeAction($action)) {
                if (!empty($response) && get_class($response) == Response::class) {
                    $response->setStatusCode(403);
                } elseif (!empty($response) && $response instanceof SocketResponse) {
                    return $responseSocketFunc($response, 403);
                }
            }
            $action = isset($errorPageArr[1]) ? $errorPageArr[1] : '';
            if (empty($action) || !method_exists($obj, $action)) {
                if (!empty($response) && $response instanceof SocketResponse) {
                    return $responseSocketFunc($response, 403);
                }
                return '404';
            }
        } else {
            if (!empty($response) && $response instanceof SocketResponse) {
                return $responseSocketFunc($response, 404);
            }
            return '404';
        }
        return '404';
    }

    protected function responseSession($response)
    {
        if (Session::getInstance()->isOpen()
            && get_class($response) == Response::class
            && $this->request instanceof RequestHttp
        ) {
            /**
             * @var Response $response
             */
            $sessionId = Session::getInstance()->getSessionId();
            $sessionName = ManagerServer::getInstance()->getServerConfig('session.name', 'SpringPHPSession');
            $httpOnly = ManagerServer::getInstance()->getServerConfig('session.httpOnly', false);
            $session_path = ManagerServer::getInstance()->getServerConfig('session.path', '/');
            $domain = Session::getInstance()->getDomain();
            $path = Session::getInstance()->getPath();
            !empty($path) && $session_path = $path;
            if (empty($domain)) {
                $host = $this->request->getHost();
                $host = preg_replace([
                    '/:80$/', '/:443$/'
                ], '', $host);
            } else {
                $host = $domain;
            }
            $maxAgeStr = '';
            $maxAge = Session::getInstance()->getMaxAge();
            !is_string($maxAge) && $maxAgeStr = ';Max-Age=' . $maxAge;
            if ($httpOnly) {
                $response->setHeader('Set-Cookie', $sessionName . '=' . $sessionId . ';domain=' . $host . $maxAgeStr . ';path=' . $session_path . ';HttpOnly');
            } else {
                $response->setHeader('Set-Cookie', $sessionName . '=' . $sessionId . ';domain=' . $host . $maxAgeStr . ';path=' . $session_path);
            }
            Session::getInstance()->end();
        }
    }
}