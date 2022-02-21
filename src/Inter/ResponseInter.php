<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Inter;
/**
 * Created by PhpStorm.
 * User: 11070
 * Date: 2022/2/20
 * Time: 21:56
 */
interface ResponseInter
{
    public function setStatusCode($http_code = 200, $reason = null);

}