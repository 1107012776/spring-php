<?php
namespace SpringPHP\Inter;
abstract class Task{
    abstract public function before();
    abstract public function exec();
    abstract public function after();
    abstract public function finish();
}