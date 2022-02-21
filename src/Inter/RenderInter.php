<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Inter;
interface RenderInter
{
    public function render(string $template, ?array $data = null, ?array $options = null): ?string;

    public function onException(\Throwable $throwable, $arg): string;
}