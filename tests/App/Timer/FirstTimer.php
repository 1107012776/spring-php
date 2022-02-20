<?php

namespace App\Timer;

use SpringPHP\Crontab\Timer;
use SpringPHP\Inter\TimerInter;

class FirstTimer extends Timer implements TimerInter
{
    public $ms = 1000;

    protected function crontabRule(): string
    {
        // 定义执行规则 根据Crontab来定义
        return '*/1 * * * *';
    }

    protected function before()
    {

    }

    protected function exec()
    {
        $memPercent = memory_get_usage(); //计算内存使用率
        echo date('Y-m-d H:i:s', time()) . ' ' . $this->crontabRule() . '定时任务-当前内存使用率：' . $memPercent . "\n";
    }

    protected function after()
    {

    }

    protected function onException(\Throwable $throwable, $arg = null)
    {

    }

}
