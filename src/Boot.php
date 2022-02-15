<?php

namespace SpringPHP;


use SpringPHP\Command\Command;
use SpringPHP\Core\SpringContext;

//swoole_process::wait
//https://www.kancloud.cn/fage/swoole_extension/691402
class Boot
{
    public static $workers;
    public static $masterPid = 0;

    /**
     * 初始化
     */
    public static function init()
    {
        SpringContext::resetConfig();
        \SpringPHP\Command\Command::parse(function ($daemonize = 0) {
            if ($daemonize == 1) { // //守护模式开启
                $process = new \Swoole\Process(function (\Swoole\Process $worker) {
                    static::exec();
                });

                $pid = $process->start();
                if ($pid > 0) {
                    exit(0);
                } elseif ($pid == -1) {
                    exit('Failed to enable Guardian Mode'); //守护模式开启失败
                }
            } else {
                static::exec();
            }
        });
    }

    /**
     * 执行
     */
    public static function exec()
    {
        ignore_user_abort(true);
        $logo = <<<LOGO
////////////////////////////////////////////////////////////////////
//      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^        //
//                          SpringPHP                             //
//            https://github.com/1107012776/spring-php            //
////////////////////////////////////////////////////////////////////
LOGO;
        echo $logo . PHP_EOL;

        $servers = SpringContext::$app->getConfig('servers');
        $pid_file = SpringContext::$app->getConfig('settings.pid_file');
        static::$masterPid = posix_getpid();
        foreach ($servers as $index => $serverConfig) {
            if (static::$masterPid !== posix_getpid()) {
                exit(0);
            }
            $serverConfig['index'] = $index;  //索引
            static::swooleCreateOneWorker($serverConfig);
        }
        if (static::$masterPid !== posix_getpid()) {
            exit(0);
        }
        @cli_set_process_title('spring-php');
        @file_put_contents($pid_file, static::$masterPid);
        Command::signalHandlerRegister();  //主进程信号注册
        static::monitorWorkers();
    }

    protected static function swooleCreateOneWorker($serverConfig)
    {
        $process = new \Swoole\Process(function (\Swoole\Process $process) use ($serverConfig) {
            $serverConfig['process'] = $process;
            switch ($serverConfig['type']) {
                case \SpringPHP\Server\Server::SERVER_HTTP:
                    \SpringPHP\Server\HttpServer::start($serverConfig);
                    break;
            }
        });
        $process->name('spring-php listen:' . $serverConfig['host'] . ':' . $serverConfig['port']);
        $pid = $process->start();
        self::$workers[(int)$pid] = $serverConfig;
    }


    /**
     * Monitor all child processes.
     * 监视所有子进程。
     * @return void
     */
    public static function monitorWorkers()
    {
        while (1) {
            // Calls signal handlers for pending signals.调用待处理信号的信号处理程序
            pcntl_signal_dispatch();
            // Suspends execution of the current process until a child has exited, or until a signal is delivered
            //暂停执行当前进程，直到孩子退出，直到信号被传送
            $res = \Swoole\Process::wait(true);
            //array('code' => 0, 'pid' => 15001)
            echo date('Y-m-d H:i:s', time()) . ' current parent_pid=' . posix_getpid() . ' exit worker pid=' . var_export($res, true) . PHP_EOL;
            file_put_contents(SpringContext::config('settings.runtime_path') . '/system' . date('Ymd') . '.log', date('Y-m-d H:i:s', time()) . ' current parent_pid=' . posix_getpid() . ' exit worker pid=' . var_export($res, true) . PHP_EOL, FILE_APPEND);
            // Calls signal handlers for pending signals again. //再次呼叫待处理信号的信号处理程序。
            pcntl_signal_dispatch();
            // If a child has already exited. 如果一个孩子已经退出了。
            if (!empty($res['pid']) && $res['pid'] > 0) {  //退出一个子进程
                unset(self::$workers[(int)$res['pid']]);
                if (empty(self::$workers)) {
                    exit(0);
                }
            } elseif (posix_getpid() == static::$masterPid
                && empty($res)) {
                if (empty(self::$workers)) {
                    exit(0);
                }
            } elseif (empty($res)) {
                exit(0);
            } else {
                /* // If shutdown state and all child processes exited then master process exit.
                 if (self::$_status === self::STATUS_SHUTDOWN && !self::getAllWorkerPids()) {
                     self::exitAndClearAll();
                 }*/
            }
        }
    }
}

