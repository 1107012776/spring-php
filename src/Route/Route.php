<?php

namespace SpringPHP\Route;
class Route
{
    /**
     * 提供匹配的uri
     * @var String
     */
    private $pattern = null;

    /**
     * 提供匹配uri的数组形式
     * @var String
     */
    private $pattern_arr = null;

    /**
     * 存储在本Route的数据
     * @var array
     */
    private $storage = array();

    /**
     * 可接受的访问HTTP方法
     * @var array
     */
    private $methods = [
        'GET', 'POST', 'PUT', 'DELETE', 'PATCH'
    ];

    /**
     * 最后的匹配获得的参数
     * @var array
     */
    private $params;

    /**
     * 构造函数
     * @param $pattern  string  匹配的路由规则 eg: /users/:id/edit
     * @param $storage    array   本路由存储的信息
     * @param null $methods array   本路由可接受的HTTP方法
     */
    public function __construct($pattern, $storage, $methods = null)
    {
        $this->pattern = $pattern;
        $this->storage = $storage;
        if (!is_null($methods)) {
            $this->methods = (array)$methods;
        }
    }

    /**
     * 重新设置本路由接受的HTTP方法
     */
    public function via()
    {
        $methods = (array)func_get_args();
        if (count($methods) === 1 and is_array($methods[0])) {
            $methods = $methods[0];
        }
        $this->methods = array_map('strtoupper', $methods);
    }

    /**
     * 将URI转换成可用的数组形式
     */
    private function uriToArray($uri)
    {
        return preg_split('|(?mi-Us)/+|', trim($uri, '/'));
    }

    /**
     * 返回Route RUI中匹配得到的参数数组
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * 获取存入本Route的信息
     * @return array
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * 获取本路由的允许匹配的URI
     * @return String
     */
    public function getUri()
    {
        return $this->pattern;
    }

    /**
     * 获取本路由允许的Method方法
     * @return array
     */
    public function getMethods()
    {
        return array_unique($this->methods);
    }

    /**
     * 判断uri是否与本路由匹配
     * @param $request_uri string 请求的URI    e.g: /user/1/edit
     * @param $request_method
     * @return boolean
     */
    public function match($request_uri, $request_method, Router $router = null)
    {
        if (!empty($router)) {
            foreach ($router->getPseudoStatic() as $item) {
                $request_uri = preg_replace(['/\.' . $item . '$/i'], '', $request_uri);  //伪静态处理
                $this->pattern = preg_replace(['/\.' . $item . '$/i'], '', $this->pattern);  //伪静态处理
            }
        }
        // 不是这个路由可接受的HTTP方法
        if (!$this->allowMethod($request_method)) {
            return false;
        }
        if ($this->pattern_arr === null) {
            $this->pattern_arr = $this->uriToArray($this->pattern);
        }
        $uri_arr = $this->uriToArray($request_uri); //将此次Request的uri转换成array
        $maximum = count($this->pattern_arr); //当前pattern可接受最大节点数
        //当前请求的节点个数如果多于patter可接受的最大节点个数，直接返回不匹配
        if (count($uri_arr) > $maximum) {
            return false;
        }
        //查找出uri中的参数节点 regular =  :\w+\??
        preg_match_all('|(?mi-Us):\\w+\\??|', $this->pattern, $rxMatches);
        foreach ($this->pattern_arr as $key => $value) {
            //如果当前节点是参数节点
            if (in_array($value, $rxMatches[0])) {
                $param_name = trim($value, ':?'); //参数节点名
                // 可选参数节点
                if (substr($value, -1) == '?') {
                    if (array_key_exists($key, $uri_arr)) {
                        $this->params[$param_name] = $uri_arr[$key];
                        continue;
                    } else {
                        return true;
                    }
                } // 普通参数节点
                else {
                    if (array_key_exists($key, $uri_arr)) {
                        $this->params[$param_name] = $uri_arr[$key];
                        continue;
                    } else {
                        return false;
                    }
                }
            }
            //普通无参数节点
            $uri_value = array_key_exists($key, $uri_arr) ? $uri_arr[$key] : null;
            if ($value != $uri_value) {
                return false;
            }
        }
        return true;
    }

    /**
     * 判断参数方法是否是本路由可接受的
     * @param $methods string/array HTTP方法
     * @return bool
     */
    public function allowMethod($methods)
    {
        $methods = array_map('strtoupper', (array)$methods);
        foreach ((array)$methods as $method) {
            if ($method == 'HEAD') {
                $method = 'GET';
            }
            if (in_array($method, $this->methods)) {
                return true;
            }
        }
        return false;
    }
}
