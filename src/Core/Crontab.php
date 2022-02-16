<?php

namespace SpringPHP\Core;

use SpringPHP\Component\Singleton;

use Swoole\Server;
use Swoole\Timer;

class Crontab
{
    use Singleton;
    private $config; //serverConfig
    private $count = 1;

    function attachServer(Server $server, $config = [])
    {
        $this->config = $config;
        $open = SpringContext::config('servers.' . $config['index'] . '.crontab.open', false);
        if (empty($open)) {
            return false;
        }
        $list = $this->__generateWorkerProcess($config);
        foreach ($list as $p) {
            $server->addProcess($p);
        }
        return true;
    }

    protected function __generateWorkerProcess($config = [])
    {
        $array = [];
        for ($i = 1; $i <= $this->count; $i++) {
            $process = new \Swoole\Process(function (\Swoole\Process $process) use ($i) {
                @cli_set_process_title('spring-php.Crontab worker pid=' . getmypid());
                SpringContext::resetConfig();
                \SpringPHP\Component\SimpleAutoload::init();
                \SpringPHP\Component\SimpleAutoload::add([
                    'App' => SPRINGPHP_ROOT . '/App'
                ]);
                $list = SpringContext::config('servers.' . $this->config['index'] . '.crontab.list', []);
                foreach ($list as $val) {
                    $timer_id = \Swoole\Timer::tick($val['ms'], function ($timer) use ($val, &$timer_id) {
                        /**
                         * @var \SpringPHP\Inter\TimerInter $obj
                         */
                        $class = $val['class'];
                        if (!class_exists($class)) {
                            \Swoole\Timer::clear($timer_id);
                            return;
                        }
                        $obj = new $class($val);
                        $response = $obj->run();
                        if (!empty($response)) {
                            echo is_string($response) ? $response:var_export($response, true).PHP_EOL;
                        }
                    });
                }
                \Swoole\Event::wait();
            });
            $process->name('worker');
            $array[$i] = $process;
        }
        return $array;
    }


}