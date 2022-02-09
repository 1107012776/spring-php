<?php

namespace SpringPHP\Core;
class SpringContext
{
    /**
     * @var SpringContext
     */
    public static $app;
    protected $config;

    public static function init($config)
    {
        if (empty(static::$app)) {
            static::$app = new static($config);
        }
        return static::$app;
    }

    protected function __construct($config)
    {
        $this->config = $config;
    }

    public function getConfig($key = '', $default = '')
    {
        if (strpos($key, '.') === false) {
            if (!empty($key)) {
                return isset($this->config[$key]) ? $this->config[$key] : $default;
            }
            return $this->config;
        }
        $keys = explode('.', $key);
        $config = $this->config;
        $count = count($keys);
        foreach ($keys as $i => $k) {
            if (!isset($config[$k])) {
                return $default;
            } else {
                if ($i == $count - 1) {
                    return $config[$k];
                }
                $config = $config[$k];
            }
        }
        return $default;
    }
}