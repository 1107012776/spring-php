<?php
//https://cloud.tencent.com/developer/article/1693964
//php pack()函数详解与示例
//cnblogs.com/xiaoleiel/p/8324122.html
class UnixSocketClient
{
    public static function init()
    {
        $socket = new \Swoole\Coroutine\Socket(AF_UNIX, SOCK_STREAM, 0);
        go(function () use ($socket) {
            $retval = $socket->connect("/tmp/server.sock");
            var_dump($retval);
            while ($retval) {
                $socket->send("hello");
                $data = $socket->recv();
                echo "server recv: " . $data . PHP_EOL;
                if (empty($data)) {
                    $socket->close();
                    break;
                }
                \Swoole\Coroutine::sleep(1.0);
            }
        });
//       \Swoole\Event::wait(); //https://wiki.swoole.com/wiki/page/1081.html
    }
}

UnixSocketClient::init();