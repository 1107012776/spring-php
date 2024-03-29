<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Inter;

interface RequestInter
{
    public function getUri();

    /**
     * @return \Swoole\Server
     */
    public function managerServer();

    public function header();

    public function server();

    public function get($key, $default = '');

    public function post($key, $default = '');

    public function params($key, $default = '');

    public function setParams($params = []);

    //获取包含请求头和内容的
    public function getData();

    //获取包含请求头和内容的
    public function getContent();

    //获取input输入
    public function rawContent();

    public function files();

    public function tmpfiles();

    public function getProcess();

    public function getConfig();

    public function getModuleName();

    /**
     * 请求模式
     * @return string
     */
    public function method();

    public function getClientIp();
}