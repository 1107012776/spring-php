<?php

namespace SpringPHP\Inter;
interface TaskInterface
{
    public function __construct($data);
    public function before($taskId = 0, $workerIndex = 0);
    public function run($taskId = 0, $workerIndex = 0);
    public function after($taskId = 0, $workerIndex = 0);
    public function finish($taskId = 0, $workerIndex = 0);
    public function onException(\Throwable $throwable, $arg = []);
}