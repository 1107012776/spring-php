<?php

namespace SpringPHP\Template;


class RenderTcpWorker
{
    /**
     * @var RenderTcpWorker
     */
    public static $master_worker;  //主进程
    public static $master_socket;  //监听的socket端口套节字 resource

    /**
     * 所有socket套接字数组
     * @var array
     */
    public static $allSockets = [];
    protected $ip = '0.0.0.0';
    protected $port = 50000;
    protected $swoole_process;


    public function __construct($ip = "0.0.0.0", $port = 65501, \Swoole\Process $swoole_process = null)
    {
        set_time_limit(0);
        $this->ip = $ip;
        $this->port = $port;
        $this->swoole_process = $swoole_process;

    }


    /**
     * [读取get或post请求中的url，返回相应的文件]
     * @param  [string]
     * @return [string]
     * http头
     * method url protocols
     */
    public function request($string)
    {
        $tpl = unserialize($string);
        if (isset($tpl['cmd'])
            && $tpl['cmd'] == 'restart'
        ) {
            foreach (static::$allSockets as $index => $socket) {
                fclose($socket);
                unset(static::$allSockets[$index]);
            }
            $this->swoole_process->exit(0);
        }
        return Render::getInstance()->trigger($tpl);
    }


    /**
     * 运行多进程模式
     */
    public function run()
    {
        @cli_set_process_title('spring-php RenderWorker master process pid=' . posix_getpid());
        self::$master_worker = $this;
        self::$master_socket = stream_socket_server("tcp://" . $this->ip . ":" . $this->port, $errno, $errstr);
        if (!self::$master_socket) {
            echo "$errstr ($errno)<br />\n";
        }
        stream_set_blocking(self::$master_socket, 0); //设置为非阻塞
        self::$allSockets[(int)self::$master_socket] = self::$master_socket;
        $this->startWorker();
    }

    public function closeSocket($socket)
    {
//        echo 'exit one socket ' . (int)$socket . "\r\n";
        unset(self::$allSockets[(int)$socket]);
        fclose($socket);
    }

    public function startWorker()
    {
        @cli_set_process_title('spring-php RenderWorker worker pid=' . posix_getpid() . ' listen:' . $this->ip . ":" . $this->port);
        while (1) {
            if (empty(self::$allSockets)) {
                break;
            }
            $write = $except = null;
            $read = self::$allSockets;
//            echo 'blocking pid=' . posix_getpid() . "\r\n";
            stream_select($read, $write, $except, NULL);  //阻塞在这边，这边不判断可写的情况
            foreach ($read as $index => $socket) {
                if ($socket === self::$master_socket) {
                    $new_socket = stream_socket_accept($socket);  //接收的新连接被别的进程处理了
                    if (empty($new_socket)) {
                        continue;
                    }
                    self::$allSockets[(int)$new_socket] = $new_socket;
                } else {
                    $string = fread($socket, 20480);
                    if ($string === '' || $string === false) {  //客户端已经退出了
                        $this->closeSocket($socket);
                        continue;
                    }
                    $data = $this->request($string);
                    $num = fwrite($socket, $data);
 /*                   if ($num == 0) {
                        echo "WRITE ERROR:" . "\n";
                    } else {
                        echo "request already succeed\n";
                    }*/
                    $this->closeSocket($socket);
                }
            }
        }

    }

    public static function start($ip = "0.0.0.0", $port = 65501, \Swoole\Process $process)
    {
        $server = new static($ip, $port, $process);
        $server->run();
    }
}