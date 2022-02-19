<?php

namespace SpringPHP\Inter;
interface TimerInter
{
    public function init(&$item);

    public function validate(&$item);

    public function run();

    public function isSuccess();
}