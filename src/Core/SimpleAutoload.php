<?php

namespace SpringPHP\Core;
class SimpleAutoload
{
    protected static $rules = [];

    public static function add($rule = [])
    {
        array_push(static::$rules, $rule);
    }

    public static function load($classname)
    {
        $fileArr = [];
        foreach (static::$rules as $index => $val) {
            foreach ($val as $k => $v) {
                $filename = sprintf('%s.php', str_replace($k, $v, $classname));
                $filename = str_replace('\\', '/', $filename);
                if (is_file($filename)) {
                    $fileArr[] = $filename;
                }
            }
        }
        foreach ($fileArr as $filename) {
            require_once $filename;
        }
    }

    public static function init()
    {
        spl_autoload_register([SimpleAutoload::class, 'load']);
    }
}
