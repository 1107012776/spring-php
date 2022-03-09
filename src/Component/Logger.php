<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Component;

/**
 * Created by PhpStorm.
 * User: 11070
 * Date: 2022/2/19
 * Time: 19:46
 */
class Logger
{
    use Singleton;

    public function log($content, $filename = 'system', $type = 'info', $isLockWrite = false)
    {
        $filename = $filename . '-' . date('Ymd');
        !is_string($content) && $content = var_export($content, true);
        $runtime_path = \SpringPHP\Core\SpringContext::config('settings.runtime_path');
        if ($isLockWrite) {  //可以防止高并发写入chunk size 混杂写入问题，$content长度超大时候需要锁定处理
            return file_put_contents($runtime_path . '/' . $filename . '.log', date('Y-m-d H:i:s') . '【' . $type . '】' . ' ' . $content . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
        return file_put_contents($runtime_path . '/' . $filename . '.log', date('Y-m-d H:i:s') . '【' . $type . '】' . ' ' . $content . PHP_EOL, FILE_APPEND);
    }
}