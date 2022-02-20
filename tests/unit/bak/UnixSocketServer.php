<?php
//https://cloud.tencent.com/developer/article/1693964

class UnixSocketServer
{
    public static function init()
    {
        $socket = new  \Swoole\Coroutine\Socket(AF_UNIX, SOCK_STREAM, 0);
        $socket->bind("/tmp/server.sock");
        $socket->listen(2048);

        go(function () use ($socket) {
            while (true) {
                echo "Accept: \n";
                $client = $socket->accept();
                if ($client === false) {
                    var_dump($socket->errCode);
                } else {
                    Swoole\Event::add($client, function ($client) {
                        if (!$client->checkLiveness()) {
                            $client->close();
                            Swoole\Event::del($client);
                            return;
                        }
                        echo $client->fd . "****" . $client->recv() . PHP_EOL;
                        $client->send("world");
                    });
                }
            }
        });
    }
}

UnixSocketServer::init();
