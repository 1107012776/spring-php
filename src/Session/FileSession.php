<?php

namespace SpringPHP\Session;

use SpringPHP\Core\SpringContext;
use SpringPHP\Inter\SessionInter;

class FileSession implements SessionInter
{

    protected $data = [];
    protected $id = '';
    protected $domain = '';
    protected $path = '';
    protected $maxAge = '';
    protected $isOpen = false;

    public function __construct($id = '')
    {
        $this->setSessionId($id);
    }

    public function isOpen()
    {
        return $this->isOpen;
    }

    public function getSessionId()
    {
        if (empty($this->id)) {
            $this->id = uniqid('session_', true) . md5(getmypid());
        }
        return $this->id;
    }

    public function setSessionId($id)
    {
        return $this->id = $id;
    }

    public function get($key, $default = '')
    {
        return isset($this->data[$key]) ? $this->data[$key] : $default;
    }

    public function set($key, $value)
    {
        return $this->data[$key] = $value;
    }

    public function start()
    {
        $this->isOpen = true;
        $this->getSessionId();
        $runtime_path = SpringContext::config('settings.runtime_path');
        $dir = $runtime_path . '/Session/';
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!file_exists($dir . $this->id)) {
            empty($this->data) && $this->data = [];
            return $this->id;
        }
        $content = file_get_contents($dir . $this->id);
        $this->data = json_decode($content, true);
        empty($this->data) && $this->data = [];
        return $this->id;
    }

    /**
     * @return array
     */
    public function read()
    {
        return $this->data;
    }

    public function remove($key)
    {
        unset($this->data[$key]);
        return true;
    }

    public function write()
    {
        if (!$this->isOpen) {
            return false;
        }
        $runtime_path = SpringContext::config('settings.runtime_path');
        if (!is_array($this->data)) {
            return false;
        }
        $str = json_encode($this->data, JSON_UNESCAPED_UNICODE);
        $dir = $runtime_path . '/Session/';
        return file_put_contents($dir . $this->id, $str, LOCK_EX);
    }

    public function close()
    {
        $this->isOpen = false;
        return true;
    }

    public function end()
    {
        if (!$this->isOpen) {
            return false;
        }
        $this->write();
        $this->close();
        return true;
    }

    public function setDomain($domain)
    {
        return $this->domain = $domain;
    }

    public function getDomain()
    {
        return $this->domain;
    }

    public function setMaxAge($time)
    {
        return $this->maxAge = $time;
    }

    public function getMaxAge()
    {
        return $this->maxAge;
    }


    public function setPath($path)
    {
        return $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }


}