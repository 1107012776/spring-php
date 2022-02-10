<?php

namespace SpringPHP\Command;

use SpringPHP\Boot;
use SpringPHP\Core\SpringContext;

class Command
{
    public static function parse(callable $func = null)
    {
        global $argv;
        print_r($argv);
        $pid_file = SpringContext::$app->getConfig('settings.pid_file');
        $pid = file_get_contents($pid_file);
        if (isset($argv[1])) {
            switch ($argv[1]) {
                case 'start':
                    static::signalHandlerRegister(); //信号注册
                    !empty($func) && $func();
                    break;
                case 'stop':
                    !empty($pid) && posix_kill($pid, SIGALRM);
                    break;
                case 'reload':
                    posix_kill($pid, SIGUSR1);
//                    posix_kill($pid, SIGUSR2);
                    break;
                default:
                    static::defaultHelp();
                    break;
            }
        }else{
            var_dump('defaultHelp');
            static::defaultHelp();
        }
    }

    public static function defaultHelp(){
        echo 'start '.PHP_EOL;
        echo 'stop '.PHP_EOL;
        echo 'reload '.PHP_EOL;
    }

    /**
     * 信号处理注册
     */
    public static function signalHandlerRegister()
    {
        declare(ticks=1)
        $signal_handler = function ($signal) {
            echo "SIGALRM " . $signal . " " . date("Y-m-d H:i:s", time()) . PHP_EOL;
            switch ($signal) {
                case SIGUSR2:
                    foreach (Boot::$workers as $worker_pid => $server) {
                        $p = posix_kill($worker_pid, $signal);
                        var_dump($p);
                    }
                    break;
                case SIGUSR1:
                    foreach (Boot::$workers as $worker_pid => $server) {
                        $p = posix_kill($worker_pid, $signal);
                        var_dump($p);
                    }
                    break;
                case SIGALRM:
                    foreach (Boot::$workers as $worker_pid => $server) {
                        $p = posix_kill($worker_pid, SIGTERM);
                        var_dump($p);
                    }
                    break;
            }
        };
        //https://www.cnblogs.com/martini-d/p/9711590.html
        //安装信号触发器
        //https://www.php.net/manual/zh/function.pcntl-wait.php
        //pcntl_signal第三个参数必须是false不然信号无法及时触发
        pcntl_signal(SIGALRM, $signal_handler, false);
        pcntl_signal(SIGUSR2, $signal_handler, false);
        pcntl_signal(SIGUSR1, $signal_handler, false);
    }


}