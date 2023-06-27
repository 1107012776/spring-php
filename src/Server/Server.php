<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Server;

use SpringPHP\Crontab\Crontab;
use SpringPHP\Inter\TaskInter;
use SpringPHP\Template\Render;
use SpringPHP\Core\ManagerServer;
use SpringPHP\Core\SpringContext;


class Server
{
    public $port;
    public $host;
    public $fds = [];
    public $startFdsTimer = false;
    const SERVER_HTTP = 1;  //http
    const SERVER_WEBSOCKET = 2;  //webSocket
    const SERVER_SOCKET = 3;  //普通tcp socket
    /**
     * @var \Swoole\Server
     */
    protected $serv;

    protected $config; //serverConfig
    /**
     * @var \Swoole\Process $swoole_process
     */
    protected $swoole_process;

    public function __construct($config = [])
    {
        $this->swoole_process = $config['process'];
        $this->config = $config;
    }

    public static function workerStart()
    {
        SpringContext::resetConfig();
        \SpringPHP\Component\SimpleAutoload::init();
        \SpringPHP\Component\SimpleAutoload::add([
            'App' => SPRINGPHP_ROOT . '/App'
        ]);
    }

    public function createTaskWorker()
    {
        //处理异步任务(此回调函数在task进程中执行)
        $this->serv->on('Task', function (\Swoole\Http\Server $serv, $task_id, $reactor_id, $data) {
            $obj = is_object($data) ? $data : unserialize($data);
            if (is_object($obj) && $obj instanceof TaskInter) {
                try {
                    $obj->before($task_id);
                    $obj->run($task_id);
                    $obj->after($task_id);
                } catch (\Exception $e) {
                    $obj->onException($e, [
                        'serv' => $serv,
                        'task_id' => $task_id,
                        'reactor_id' => $reactor_id,
                        'data' => $data,
                    ]);
                }
            }
            //返回任务执行的结果
            $serv->finish(serialize($obj));
        });
        //处理异步任务的结果(此回调函数在worker进程中执行)
        $this->serv->on('Finish', function (\Swoole\Http\Server $serv, $task_id, $data) {
            $obj = unserialize($data);
            if (is_object($obj) && $obj instanceof TaskInter) {
                try {
                    $obj->finish($task_id);
                } catch (\Exception $e) {
                    $obj->onException($e, [
                        'serv' => $serv,
                        'task_id' => $task_id,
                        'data' => $data,
                    ]);
                }
            }
            $data = serialize($obj);
        });
    }

    /**
     * 初始化赋值server启动之前
     * @param $port
     * @param $config
     */
    public function init($port, $config)
    {
        $this->serv->on('start', function ($serv) {
            swoole_set_process_name('spring-php.Manager');
        });
        $this->serv->on('workerStart', array($this, 'onWorkerStart'));
        $this->createTaskWorker();
        ManagerServer::getInstance()->setServerConfig($config);
        ManagerServer::getInstance()->setServer($this->serv);
        ManagerServer::getInstance()->setMasterServer($this);
        Render::getInstance()->attachServer($this->serv, $port, $config);
        Crontab::getInstance()->attachServer($this->serv, $config);
    }

    /**
     *
     * 每个worker启动的时候
     * @param \Swoole\Server $serv
     */
    public function onWorkerStart(\Swoole\Server $serv, $worker_id)
    {
        Server::workerStart();
        if ($worker_id >= $serv->setting['worker_num']) {
            swoole_set_process_name("spring-php.task.{$worker_id} pid=" . getmypid());
        } else {
            ManagerServer::getInstance()->setUniquelyIdentifies(uniqid(md5(getmypid())));
            \Swoole\Runtime::enableCoroutine($flags = SWOOLE_HOOK_ALL);
            swoole_set_process_name("spring-php.worker.{$worker_id} listen:" . $this->host . ':' . $this->port);
            $workerStartCallback = isset($this->config['event_worker_start']) ? $this->config['event_worker_start'] : '';
            if (is_callable($workerStartCallback)) {
                $workerStartCallback($serv, $worker_id);
            }
        }
        if ($worker_id == 0) { //重启RenderWorker Crontab
            \Swoole\Coroutine::create(function () {
                Render::getInstance()->restartWorker();
                Crontab::getInstance()->restartWorker();
            });
        }
    }

    /**
     * 获取某个全局配置(优先取server里面的)
     * @param string $key
     * @param string $default
     * @return string
     */
    public function getSettingsConfig($key, $default = '')
    {
        $notMsg = 'Server setting does not exist';
        $value = \SpringPHP\Core\SpringContext::config('servers.' . $this->config['index'] . '.' . $key, $notMsg);
        if ($notMsg === $value) {
            return \SpringPHP\Core\SpringContext::config($key, $default);
        }
        return $value;
    }

    public static function start($config = [])
    {
        return new static($config);
    }
}