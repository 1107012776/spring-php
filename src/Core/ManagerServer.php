<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Core;

use SpringPHP\Component\Singleton;
use SpringPHP\Server\Server;

class ManagerServer
{
    use Singleton;
    /**
     * @var array $serverConfig
     */
    protected $serverConfig;
    /**
     * @var \Swoole\Server $server
     */
    protected $server;

    /**
     * @var Server $masterServer
     */
    protected $masterServer;

    protected $uniquelyIdentifies = '';  //进程唯一标识

    /**
     * @return string
     */
    public function getUniquelyIdentifies(): string
    {
        return $this->uniquelyIdentifies;
    }

    /**
     * @param string $uniquelyIdentifies
     */
    public function setUniquelyIdentifies(string $uniquelyIdentifies): void
    {
        $this->uniquelyIdentifies = $uniquelyIdentifies;
    }

    /**
     * @return Server
     */
    public function getMasterServer(): Server
    {
        return $this->masterServer;
    }

    /**
     * @param Server $masterServer
     */
    public function setMasterServer(Server $masterServer): void
    {
        $this->masterServer = $masterServer;
    }


    /**
     * @param  $key
     * @param  $default
     * @return array|string
     */
    public function getServerConfig($key = '', $default = '')
    {
        if (!empty($key)) {
            return SpringContext::$app->getConfig('servers.' . $this->getServerConfigIndex() . '.' . $key, $default);
        }
        return $this->serverConfig;
    }


    public function setServerConfig($serverConfig): void
    {
        $this->serverConfig = $serverConfig;
    }

    public function getServerConfigIndex(): int
    {
        return $this->serverConfig['index'];
    }


    /**
     * @return \Swoole\Server
     */
    public function getServer()
    {
        return $this->server;
    }


    public function setServer($server): void
    {
        $this->server = $server;
    }


}