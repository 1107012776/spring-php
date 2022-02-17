<?php

namespace SpringPHP\Core;

use SpringPHP\Component\Singleton;

use Swoole\Server;

class Crontab
{
    use Singleton;
    private $config; //serverConfig
    private $count = 1; //强制只有一个定时任务进程，多个暂时不支持

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

    public function restartWorker()
    {
        $open = SpringContext::config('servers.' . $this->config['index'] . '.crontab.open', false);
        if (empty($open)) {
            return false;
        }
        $runtime_path = SpringContext::config('settings.runtime_path');
        $file = $runtime_path . "/spring-php-swoole-" . $this->config['index'] . "-timer-restart.log";
        file_put_contents($file, date('Y-m-d H:i:s', time()).PHP_EOL);
        return true;
    }

    public function getRestartFile()
    {
        $runtime_path = SpringContext::config('settings.runtime_path');
        $file = $runtime_path . "/spring-php-swoole-" . $this->config['index'] . "-timer-restart.log";
        return $file;
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
                $timerArr = [];
                foreach ($list as $val) {
                    $timer_id = \Swoole\Timer::tick($val['ms'], function ($timer) use ($val, &$timer_id, &$timerArr) {
                        /**
                         * @var \SpringPHP\Inter\TimerInter $obj
                         */
                        $class = $val['class'];
                        if (!class_exists($class)) {
                            unset($timerArr[$timer_id]);
                            \Swoole\Timer::clear($timer_id);
                            return;
                        }
                        $obj = new $class($val);
                        $response = $obj->run();
                        if (!empty($response)) {
                            echo is_string($response) ? $response : var_export($response, true) . PHP_EOL;
                        }
                    });
                    $timerArr[$timer_id] = $timer_id;
                }
                $timer_id = \Swoole\Timer::tick(1000, function ($timer) use ($timerArr, &$timer_id) {
                    $file = $this->getRestartFile();
                    if (file_exists($file)) {
                        unlink($file);
                        foreach ($timerArr as $value) {
                            \Swoole\Timer::clear($value);
                        }
                        \Swoole\Timer::clear($timer_id);
                    }
                });
                \Swoole\Event::wait();
            });
            $process->name('worker');
            $array[$i] = $process;
        }
        return $array;
    }


}