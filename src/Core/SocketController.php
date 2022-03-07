<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Core;

abstract class  SocketController extends Controller
{
    protected function getFd()
    {
        if (method_exists($this->request, 'getFd')) {
            return $this->request->getFd();
        }
        return 0;
    }
}