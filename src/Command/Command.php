<?php

namespace SpringPHP\Command;

use SpringPHP\Boot;
use SpringPHP\Core\SpringContext;

class Command
{
    public static function parse(callable $func = null)
    {
        global $argv;
        $pid_file = SpringContext::$app->getConfig('settings.pid_file');
        $pid = file_get_contents($pid_file);
        if (isset($argv[1])) {
            switch ($argv[1]) {
                case 'start':
                    if (\Swoole\Process::kill($pid, PRIO_PROCESS)) {
                        echo 'The current project is running' . PHP_EOL;
                        exit(0);
                    }
                    $daemonize = 0;
                    if (isset($argv[2])
                        && $argv[2] == '-d'
                    ) {
                        $daemonize = 1;
                    }
                    !empty($func) && $func($daemonize);
                    break;
                case 'stop':
                    !empty($pid) && posix_kill($pid, SIGALRM);
                    break;
                case 'reload':
                    posix_kill($pid, SIGUSR1);
//                    posix_kill($pid, SIGUSR2);
                    break;
                case 'queryProcessNum':  //查询某服务进程数
                    echo static::queryProcessNum(isset($argv[2]) ? $argv[2] : '') . PHP_EOL;
                    break;
                case 'queryProcess':  //查询某服务进程
                    $res = static::queryProcess(isset($argv[2]) ? $argv[2] : '');
                    if (is_array($res)) {
                        foreach ($res as $row) {
                            echo $row . PHP_EOL;
                        }
                    } else {
                        echo var_export($res, true) . PHP_EOL;
                    }
                    break;
                case 'process':  //查询某服务进程
                    $res = static::queryProcess('spring-php');
                    if (is_array($res)) {
                        foreach ($res as $row) {
                            echo $row . PHP_EOL;
                        }
                    } else {
                        echo var_export($res, true) . PHP_EOL;
                    }
                    break;
                default:
                    static::defaultHelp();
                    break;
            }
        } else {
            static::defaultHelp();
        }
    }

    public static function defaultHelp()
    {
        echo 'Do you need to execute ?' . PHP_EOL;
        echo '    start ' . PHP_EOL;
        echo '    stop ' . PHP_EOL;
        echo '    reload ' . PHP_EOL;
        echo '    queryProcessNum ' . PHP_EOL;
        echo '    queryProcess ' . PHP_EOL;
        echo '    process ' . PHP_EOL;
    }

    /**
     * 信号处理注册
     */
    public static function signalHandlerRegister()
    {
        declare(ticks=1)
        $signal_handler = function ($signal) {
            echo "signal=" . $signal . " " . date("Y-m-d H:i:s", time()) . PHP_EOL;
            file_put_contents(SpringContext::config('settings.runtime_path') . '/system' . date('Ymd') . '.log', "signal=" . $signal . " " . date("Y-m-d H:i:s", time()) . PHP_EOL, FILE_APPEND);
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

    public static function queryProcessNum($service)
    {
        $res = array();
        exec("ps -ef | grep " . $service . " | wc -l", $res);
        return trim($res[0]) - 2;
    }

    public static function queryProcess($service)
    {
        exec("ps -auxf | grep " . $service, $res);
        return $res;
    }


}