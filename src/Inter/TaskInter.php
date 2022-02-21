<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */
namespace SpringPHP\Inter;
interface TaskInter
{
    public function __construct($data);

    public function before($taskId = 0, $workerIndex = 0);

    public function run($taskId = 0, $workerIndex = 0);

    public function after($taskId = 0, $workerIndex = 0);

    public function finish($taskId = 0, $workerIndex = 0);

    public function onException(\Throwable $throwable, $arg = []);
}