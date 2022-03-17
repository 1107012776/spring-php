<?php

namespace SpringPHP\Session;

use SpringPHP\Component\Singleton;
use SpringPHP\Core\SpringContext;
use SpringPHP\Inter\SessionInter;

class Session
{
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
        return $this->getDriver()->end();
    }
}