<?php

namespace SpringPHP\Session;

use SpringPHP\Component\FileDirUtil;
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
    protected $oldHash = '';

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
            $this->id = uniqid('session_' . mt_rand(1000, 9999), true) . md5('sp:' . getmypid());
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
        $this->oldHash = sha1($content) . '_' . md5($content);
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
        $dir = $runtime_path . '/Session/';
        if (empty($this->data)
            && !file_exists($dir . $this->id)
        ) {  //数据为空文件又不存在，无需创建session文件
            return false;
        }
        $str = json_encode($this->data, JSON_UNESCAPED_UNICODE);
        $lastChangeTime = @filectime($dir . $this->id);
        if ($lastChangeTime !== false) {
            $newHash = sha1($str) . '_' . md5($str);
            if (!empty($this->oldHash)
                && $this->oldHash == $newHash
                && $lastChangeTime <= time() - 60) { //数据未变更，减少io操作
                return false;
            }
        }
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

    public function gc($timeout = 3600)
    {
        $runtime_path = SpringContext::config('settings.runtime_path');
        $dir = $runtime_path . '/Session/';
        $fileUtil = new FileDirUtil();
        $list = $fileUtil->dirList($dir);
        foreach ($list as $item) {
            if (@filectime($item) <= time() - $timeout) {
                @unlink($item);
            }
        }
    }


}