<?php
/**
 * 模板
 */

namespace SpringPHP\Template;


use SpringPHP\Component\Singleton;

use SpringPHP\Core\SpringContext;
use Swoole\Coroutine;
use Swoole\Server;
use SpringPHP\Component\Protocol;

class Render
{
    use Singleton;
    private $renderWorker = [];
    private $count = 2;
    private $port = 50000;
    private $config; //serverConfig
    const SOCKET_UNIX = 'UNIX';
    const SOCKET_TCP = 'TCP';

    /**
     * 控制Accept是否协程异步
     * @param $func
     */
    public function controlAccept(callable $func)
    {
        $isAsyn = SpringContext::config('servers.' . $this->config['index'] . '.template.asynchronous', false);
        if ($isAsyn) { //smarty模板协程不安全，请勿开启异步
            Coroutine::create(function () use ($func) {
                $func();
            });
        } else {
            $func();
        }
    }

    public function trigger($tpl)
    {
        $config = $this->config;
        $callback = SpringContext::config('servers.' . $config['index'] . '.template.callback', null);
        if (!empty($callback)) {
            return $callback($tpl);
        }
        return '';
    }

    function attachServer(Server $server, $port = 50000, $config = [])
    {
        $this->config = $config;
        $open = SpringContext::config('servers.' . $config['index'] . '.template.open', false);
        if (empty($open)) {
            return false;
        }
        $this->port = $port;
        $list = $this->__generateWorkerProcess($config);
        foreach ($list as $p) {
            $server->addProcess($p);
        }
        return true;
    }

    function render($template = '', $data = [], $options = [])
    {
        if (empty($this->renderWorker)) {
            return '';
        }
        /*
         * 随机找一个进程
         */
        mt_srand();
        $id = mt_rand(1, $this->count);
        $processEnd = end($this->renderWorker);
        if ($processEnd['socketType'] == Render::SOCKET_UNIX) {
            $res = $this->unixRender($id, [
                'template' => $template,
                'data' => $data,
                'options' => $options,
            ]);
        } else {
            $res = $this->tcpRender($id, [
                'template' => $template,
                'data' => $data,
                'options' => $options,
            ]);
        }
        return $res;
    }


    public function restartWorker()
    {
        if (empty($this->renderWorker)) {
            return false;
        }
        $processEnd = end($this->renderWorker);
        for ($i = 1; $i <= $this->count; $i++) {
            if ($processEnd['socketType'] == Render::SOCKET_UNIX) {
                $this->unixRestartRender($i, [
                    'cmd' => 'restart'
                ]);
            } else {
                $this->tcpRestartRender($i, [
                    'cmd' => 'restart'
                ]);
            }
        }
        return true;
    }

    protected function __generateWorkerProcess($config = []): array
    {
        $socketType = SpringContext::config('servers.' . $config['index'] . '.template.socket_type', Render::SOCKET_UNIX);
        $this->count = SpringContext::config('servers.' . $config['index'] . '.template.count', 2);
        $array = [];
        $ip = "0.0.0.0";
        $port = $this->port;
        for ($i = 1; $i <= $this->count; $i++) {
            $process = new \Swoole\Process(function (\Swoole\Process $process) use ($i, $ip, $port, $socketType) {
                SpringContext::resetConfig();
                \SpringPHP\Component\SimpleAutoload::init();
                \SpringPHP\Component\SimpleAutoload::add([
                    'App' => SPRINGPHP_ROOT . '/App'
                ]);
                if ($socketType == Render::SOCKET_UNIX) {
                    RenderUnixWorker::start($port + $i, $process);
                } else {
                    RenderTcpWorker::start($ip, $port + $i, $process);
                }
            });
            $process->name('worker');
            $this->renderWorker[$i] = [
                'socketType' => $socketType,
                'ip' => $ip,
                'id' => $port + $i,
            ];
            $array[$i] = $process;
        }
        return $array;
    }


    function unixRender($id, $requestData = [])
    {
        $socket = new \Swoole\Coroutine\Socket(AF_UNIX, SOCK_STREAM, 0);
        $runtime_path = SpringContext::config('settings.runtime_path');
        $retval = $socket->connect($runtime_path . "/spring-php-render-worker-" . ($this->port + $id) . ".sock");
        $str = '';
        $socket->send(Protocol::pack(serialize($requestData)));
        while ($retval) {
            $data = $socket->recv();
            //发生错误或对端关闭连接，本端也需要关闭
            if ($data === '' || $data === false) {
                $socket->close();
                break;
            }
            $str .= $data;
            \Swoole\Coroutine::sleep(0.001);
        }
        return $str;
    }


    public function tcpRender($id, $requestData = [])
    {
        $port = $this->port;
        $socket = new \Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
        $ip = "0.0.0.0";
        $retval = $socket->connect($ip, $port + $id);
        $str = '';
        if (empty($retval)) {
            return $str;
        }
        $n = $socket->send(serialize($requestData));
        if (empty($n)) {
            return $str;
        }
        while ($retval) {
            $data = $socket->recv();
            //发生错误或对端关闭连接，本端也需要关闭
            if ($data === '' || $data === false) {
                $socket->close();
                break;
            }
            $str .= $data;
            \Swoole\Coroutine::sleep(0.001);
        }
        return $str;
    }


    function unixRestartRender($id, $requestData = [])
    {
        $socket = new \Swoole\Coroutine\Socket(AF_UNIX, SOCK_STREAM, 0);
        $runtime_path = SpringContext::config('settings.runtime_path');
        $retval = $socket->connect($runtime_path . "/spring-php-render-worker-" . ($this->port + $id) . ".sock");
        $str = '';
        $socket->send(Protocol::pack(serialize($requestData)));
        while ($retval) {
            $data = $socket->recv();
            //发生错误或对端关闭连接，本端也需要关闭
            if ($data === '' || $data === false) {
                $socket->close();
                break;
            }
            $str .= $data;
            \Swoole\Coroutine::sleep(0.001);
        }
        return $str;
    }


    public function tcpRestartRender($id, $requestData = [])
    {
        $port = $this->port;
        $socket = new \Swoole\Coroutine\Socket(AF_INET, SOCK_STREAM, 0);
        $ip = "0.0.0.0";
        $retval = $socket->connect($ip, $port + $id);
        $str = '';
        if (empty($retval)) {
            return $str;
        }
        $n = $socket->send(serialize($requestData));
        if (empty($n)) {
            return $str;
        }
        while ($retval) {
            $data = $socket->recv();
            //发生错误或对端关闭连接，本端也需要关闭
            if ($data === '' || $data === false) {
                $socket->close();
                break;
            }
            $str .= $data;
            \Swoole\Coroutine::sleep(0.001);
        }
        return $str;
    }


}