<?php

namespace SpringPHP;


use SpringPHP\Core\SpringContext;

class Boot
{
    public static $workers;
    public static $masterPid = 0;
    /**
     * 初始化
     */
    public static function init()
    {
        $logo = <<<LOGO
////////////////////////////////////////////////////////////////////
//                          _ooOoo_                               //
//                         o8888888o                              //
//                         88" . "88                              //
//                         (| ^_^ |)                              //
//                         O\  =  /O                              //
//                      ____/`---'\____                           //
//                    .'  \\|     |//  `.                         //
//                   /  \\|||  :  |||//  \                        //
//                  /  _||||| -:- |||||-  \                       //
//                  |   | \\\  -  /// |   |                       //
//                  | \_|  ''\---/''  |   |                       //
//                  \  .-\__  `-`  ___/-. /                       //
//                ___`. .'  /--.--\  `. . ___                     //
//            \  \ `-.   \_ __\ /__ _/   .-` /  /                 //
//      ========`-.____`-.___\_____/___.-`____.-'========         //
//                           `=---='                              //
//      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^        //
//                          SpringPHP                             //
////////////////////////////////////////////////////////////////////
LOGO;
        echo $logo . PHP_EOL;
        $configPath = SPRINGPHP_ROOT . "/App/Config/Config.php";
        $config = include_once($configPath);
        \SpringPHP\Core\SpringContext::init($config);
        $servers = SpringContext::$app->getConfig('servers');
        $pid_file = SpringContext::$app->getConfig('settings.pid_file');
        static::$masterPid = posix_getpid();
        @file_put_contents($pid_file, static::$masterPid);
        foreach ($servers as $server) {
            if(static::$masterPid !== posix_getpid()){
                exit(0);
            }
//            var_dump('bianli spring-php worker pid=' . posix_getpid());
            static::createOneWorker($server);
        }
        if(static::$masterPid !== posix_getpid()){
            exit(0);
        }
        static::monitorWorkers();
    }

    protected static function createOneWorker($server){
        switch ($server['type']) {
            case \SpringPHP\Server\Server::SERVER_HTTP:
                $pid = pcntl_fork();
                if ($pid > 0) {
                    self::$workers[(int)$pid] = $server;
                }else{
                    @cli_set_process_title('spring-php worker pid=' . posix_getpid());
                    \SpringPHP\Server\HttpServer::start($server['host'], $server['port']);
                }
                break;
        }
    }
    /**
     * Monitor all child processes.
     * 监视所有子进程。
     * @return void
     */
    public static function monitorWorkers()
    {
//        var_dump(self::$workers,posix_getpid());
        while (1) {
            // Calls signal handlers for pending signals.调用待处理信号的信号处理程序
            pcntl_signal_dispatch();
            // Suspends execution of the current process until a child has exited, or until a signal is delivered
            //暂停执行当前进程，直到孩子退出，直到信号被传送
            $status = 0;
            $pid = pcntl_wait($status, WUNTRACED);
            echo date('Y-m-d H:i:s', time()).' current parent_pid='.posix_getpid().' exit worker pid='.$pid.PHP_EOL;
            // Calls signal handlers for pending signals again. //再次呼叫待处理信号的信号处理程序。
            pcntl_signal_dispatch();
            // If a child has already exited. 如果一个孩子已经退出了。
            if ($pid > 0) {  //退出一个子进程，则继续开启一个子进程
                $server = self::$workers[(int)$pid];
                unset(self::$workers[(int)$pid]);
                static::createOneWorker($server);
            }if($pid == -1){
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

