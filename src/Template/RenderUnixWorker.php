<?php

namespace SpringPHP\Template;


use Swoole\Coroutine;

class RenderUnixWorker
{
    public $id;
    protected $swoole_process;

    public function __construct($id = 65501, \Swoole\Process $swoole_process = null)
    {
        set_time_limit(0);
        $this->id = $id;
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
            Coroutine::create(function () {
                Coroutine::sleep(0.001);
                $this->swoole_process->exit(0);
            });
            return '';
        }
        return Render::getInstance()->trigger($tpl);
    }


    /**
     * 运行多进程模式
     */
    public function run()
    {
        @cli_set_process_title('spring-php RenderWorker unix master process pid=' . posix_getpid());

        $socket = new  \Swoole\Coroutine\Socket(AF_UNIX, SOCK_STREAM, 0);
        $socket->bind("/tmp/spring-php-server-" . $this->id . ".sock");
        $socket->listen(2048);
        @cli_set_process_title('spring-php RenderWorker unix worker pid=' . posix_getpid());
        go(function () use ($socket) {
            while (1) {
                $client = $socket->accept(-1);
                if (!$client) {
                    return;
                }
                $this->accept($client);
            }
        });
        \Swoole\Event::wait();
    }

    public function accept(\Swoole\Coroutine\Socket $socket)
    {
        $header = $socket->recvAll(4, 1);
        if (strlen($header) != 4) {
            $socket->close();
            return;
        }
        $allLength = Protocol::packDataLength($header);
        $data = $socket->recvAll($allLength, 1);
        if (strlen($data) == $allLength) {
            $reply = null;
            try {
                $reply = $this->request($data);
            } catch (\Throwable $throwable) {
                $reply = var_export($throwable, true);
            } finally {
                $socket->sendAll($reply);
                $socket->close();
            }
        } else {
            $socket->close();
        }
    }

    public static function start($id = 65501, \Swoole\Process $process)
    {
        $server = new static($id, $process);
        $server->run();
    }
}