<?php
/**
 * 模板
 */

namespace SpringPHP\Template;


use SpringPHP\Component\Singleton;

use SpringPHP\Core\SpringContext;
use Swoole\Server;

class Render
{
    use Singleton;
    private $renderWorker = [];
    private $count = 2;
    private $port = 50000;
    const SOCKET_UNIX = 'UNIX';
    const SOCKET_TCP = 'TCP';

    function attachServer(Server $server, $port = 50000)
    {
        $this->port = $port;
        $list = $this->__generateWorkerProcess();
        foreach ($list as $p) {
            $server->addProcess($p);
        }
    }

    function render($template = '', $data = [],$options = [])
    {
        if(empty($this->renderWorker)){
            return '';
        }
        $processEnd = end($this->renderWorker);
        /*
         * 随机找一个进程
         */
        mt_srand();
        $id = mt_rand(1, $this->count);
        if($processEnd['socketType'] == Render::SOCKET_UNIX){
            $res = $this->unixRender($id, [
                'template' => $template,
                'data' => $data,
                'options' => $options,
            ]);
        }else{
            $res = $this->tcpRender($id, [
                'template' => $template,
                'data' => $data,
                'options' => $options,
            ]);
        }
        return $res;
    }

    function unixRender($id,  $requestData = [])
    {
        $socket = new \Swoole\Coroutine\Socket(AF_UNIX, SOCK_STREAM, 0);
        $retval = $socket->connect("/tmp/spring-php-server-".($this->port+$id).".sock");
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


    function restartWorker()
    {
        /*  $com = new Command();
          $com->setOp(Command::OP_WORKER_EXIT);
          $data = Protocol::pack(serialize($com));
          $server = $this->getConfig()->getServerName();
          for($i = 0;$i < $this->getConfig()->getWorkerNum();$i++){
              $sockFile = $this->getConfig()->getTempDir()."/{$server}.Render.Worker.{$i}.sock";
              Coroutine::create(function ()use($sockFile,$data){
                  $client = new UnixClient($sockFile);
                  $client->send($data);
              });
          }*/
        return true;
    }

    protected function __generateWorkerProcess(): array
    {
        $array = [];
        $ip = "0.0.0.0";
        $port = $this->port;
        for ($i = 1; $i <= $this->count; $i++) {
            $process = new \Swoole\Process(function (\Swoole\Process $process) use ($i, $ip, $port) {
                if(SpringContext::config('template.socketType',Render::SOCKET_UNIX) == Render::SOCKET_UNIX){
                    RenderUnixWorker::start($port + $i);
                }else{
                    RenderTcpWorker::start($ip,$port + $i);
                }
            });
            $process->name('RenderWorker');
            $this->renderWorker[$i] = [
                'pid' => $process->pid,
                'socketType' => SpringContext::config('template.socketType',Render::SOCKET_UNIX),
                'ip' => $ip,
                'id' => $port + $i,
            ];
            $array[$i] = $process;
        }
        return $array;
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
}