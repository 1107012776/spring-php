<?php


/**
 * 路由
 */

use SpringPHP\Inter\RequestInter;
use SpringPHP\Route\Router;

$router = new Router;
//添加一个接受Get请求的路由
$router->get('/test', function (RequestInter $request, \Swoole\Http\Response $response) {
    return 'test2133123123123123';
});

//添加一个接受Post请求的路由
$router->get('/Index/index11', '/Index/index');
$router->get('/Index/index/:id?.html', '/Index/index');
$router->get('/Index/index/:id?.tpl', '/Index/index');
$router->get('/Index/index/(\d+).tpl', '/Index/index');
//伪静态设置
$router->setPseudoStatic(['tpl', 'html']);

return $router;