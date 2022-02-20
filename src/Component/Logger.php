<?php

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

    public function log($content, $filename = 'system', $type = 'info')
    {
        $filename = $filename . '-' . date('Ymd');
        !is_string($content) && $content = var_export($content, true);
        $runtime_path = \SpringPHP\Core\SpringContext::config('settings.runtime_path');
        return file_put_contents($runtime_path . '/' . $filename . '.log', date('Y-m-d H:i:s') . '【' . $type . '】' . ' ' . $content . PHP_EOL, FILE_APPEND);
    }
}