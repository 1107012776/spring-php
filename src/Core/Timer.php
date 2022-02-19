<?php

namespace SpringPHP\Core;
abstract class Timer
{
    public $data;
    public $ms = 0;  //毫秒
    protected $response;
    protected $success = false;
    public function init(&$item){
        if(!empty($item['initialization'])){
            return false;
        }
        $item['initialization'] = true;
        $this->validate($item);
        return true;
    }


    protected function crontabRule(): string
    {
        /**
         *    *    *    *    *
         * -    -    -    -    -
         * |    |    |    |    |
         * |    |    |    |    |
         * |    |    |    |    +----- day of week (0 - 7) (Sunday=0 or 7)
         * |    |    |    +---------- month (1 - 12)
         * |    |    +--------------- day of month (1 - 31)
         * |    +-------------------- hour (0 - 23)
         * +------------------------- min (0 - 59)
         */
        // 定义执行规则 根据Crontab来定义
        return '* * * * *';
    }

    /**
     * 获取当前是星期几
     * $weekArray = ["日","一","二","三","四","五","六"];
     * $weekArray[date("w")];
     * @param $time
     * @return int
     **/
    protected function getCurrentWeek($time)
    {
        return date("w", $time);
    }

    protected function getCurrentMonth($time)
    {
        return date("m", $time);
    }

    protected function getCurrentDay($time)
    {
        return date("d", $time);
    }

    protected function getCurrentYear($time)
    {
        return date("Y", $time);
    }

    protected function getCurrentHour($time)
    {
        return date("H", $time);
    }

    protected function getCurrentMin($time)
    {
        return date("i", $time);
    }

    /**
     * 解析规则
     */
    protected function parseRule()
    {
        $rute = $this->crontabRule();
        $rutes = explode(' ', $rute);
        $rutes = array_filter($rutes);
        list($min, $hour, $day, $month, $week) = $rutes;
        return [
            $min,
            $hour,
            $day,
            $month,
            $week,
        ];
    }

    public function validate(&$item)
    {
        if (!empty($item['nextExecTime'])) {
            $nextExecTime = strtotime($item['nextExecTime']); //上一次最后执行时间
        }
        if (time() < $nextExecTime) {
            return false;
        }
        list($min, $hour, $day, $month, $week) = $this->parseRule();
        if (strpos($min, '/')) {
            $minArr = explode('/', $min);  //每多少分钟执行一次
            $nextExecTime += $minArr[1] * 60;
        } else {
            if ($min != '*') {
                if ($min != $this->getCurrentMin(time())) {
                    return false;
                }
            }
        }
        if (strpos($hour, '/')) {
            $hourArr = explode('/', $hour);  //每多少小时执行一次
            $nextExecTime += $hourArr[1] * 60 * 60;
        } else {
            if ($hour != '*') {
                if ($hour != $this->getCurrentHour(time())) {
                    return false;
                }
            }
        }
        if (strpos($day, '/')) {
            $dayArr = explode('/', $day);  //每多少天执行一次
            $nextExecTime += $dayArr[1] * 60 * 60 * 24;
        } else {
            if ($day != '*') {
                if ($day != $this->getCurrentDay(time())) {
                    return false;
                }
            }
        }
        if (strpos($month, '/')) {
            $monthArr = explode('/', $month);  //每多少月执行一次
            $nextExecTime = strtotime("+" . $monthArr[1] . " month", $nextExecTime);
        } else {
            if ($month != '*') {
                if ($month != $this->getCurrentMonth(time())) {
                    return false;
                }
            }
        }
        if ($week != '*'
            && $week != $this->getCurrentWeek(time())
        ) {
            return false;
        }
        $item['nextExecTime'] = date('Y-m-d H:i:s', $nextExecTime);
        return true;
    }

    public function __construct($params = [])
    {
        foreach ($params as $key => $val) {
            if (property_exists($this, $key)) {
                $this->$key = $val;
            }
        }
    }

    public function run()
    {
        try {
            $this->before();
            $this->exec();
            $this->after();
        } catch (\Exception $e) {
            $this->success = false;
            return $this->onException($e);
        }
        $this->success = true;
        return $this->response;
    }

    /**
     * 定时任务是否执行成功
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    abstract protected function before();

    abstract protected function exec();

    abstract protected function after();

    abstract protected function onException(\Throwable $throwable, $arg = null);

}