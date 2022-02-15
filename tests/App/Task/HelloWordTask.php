<?php

namespace App\Task;

use SpringPHP\Inter\TaskInter;

class HelloWordTask implements TaskInter
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function before($taskId = 0, $workerIndex = 0)
    {

    }

    public function run($taskId = 0, $workerIndex = 0)
    {
        echo __CLASS__ . ' task_id=' . $taskId . ' pid=' . getmypid() . '  ' . var_export($this->data, true);
    }

    public function after($taskId = 0, $workerIndex = 0)
    {

    }

    public function finish($taskId = 0, $workerIndex = 0)
    {

    }

    public function onException(\Throwable $throwable, $arg = [])
    {

    }


}