<?php

namespace SpringPHP\Session;

use SpringPHP\Component\Singleton;
use SpringPHP\Core\ManagerServer;
use SpringPHP\Core\SpringContext;
use SpringPHP\Inter\SessionInter;

class Session
{
    protected $isGc = true;
    protected $gcTime = 0;
    use Singleton;

    /**
     * @return SessionInter
     */
    protected function getDriver()
    {
        return SpringContext::$app->get(Session::class . '_driver');
    }

    public function setSessionId($id)
    {
        return $this->getDriver()->setSessionId($id);
    }

    public function getSessionId()
    {
        return $this->getDriver()->getSessionId();
    }

    public function start($driver = null)
    {
        SpringContext::$app->set(Session::class . '_driver', $driver);
        return $this->getDriver()->start();
    }

    public function get($key, $default = '')
    {
        if (!$this->isOpen()) {
            return $default;
        }
        return $this->getDriver()->get($key, $default);
    }

    public function set($key, $default)
    {
        return $this->getDriver()->set($key, $default);
    }

    public function remove($key)
    {
        return $this->getDriver()->remove($key);
    }

    public function setDomain($domain)
    {
        return $this->getDriver()->setDomain($domain);
    }

    public function getDomain()
    {
        return $this->getDriver()->getDomain();
    }

    public function setMaxAge($time)
    {
        return $this->getDriver()->setMaxAge($time);
    }

    public function getMaxAge()
    {
        return $this->getDriver()->getMaxAge();
    }

    public function setPath($path)
    {
        return $this->getDriver()->setPath($path);
    }

    public function getPath()
    {
        return $this->getDriver()->getPath();
    }

    public function isOpen()
    {
        if (empty($this->getDriver())) {
            return false;
        }
        return $this->getDriver()->isOpen();
    }

    public function close()
    {
        return $this->getDriver()->close();
    }

    public function end()
    {
        $isGcStart = ManagerServer::getInstance()->getServerConfig('session.gc_start', false);
        $isGcStart && $this->gc(ManagerServer::getInstance()->getServerConfig('session.timeout', 3600));
        return $this->getDriver()->end();
    }

    public function gc($timeout = 3600)
    {
        if (!$this->isOpen()) {
            return;
        }
        $currentTime = time();
        if ($this->isGc && $currentTime > $this->gcTime) {  //减少gc的调用次数
            $this->gcTime = time() + $timeout; //每个超时时间段gc一次
            $this->isGc = false;
            $driver = $this->getDriver();
            \Swoole\Coroutine::create(function () use ($timeout, $driver) {
                $driver->gc($timeout);
                $this->isGc = true;
            });
        }
    }
}