<?php

namespace App\WebSocket\Controller;


use App\Model\UserModel;
use App\Model\UserSessionModel;
use App\Rpc\ManagerRpc;
use SpringPHP\Core\ManagerServer;
use SpringPHP\Core\WebSocketController;


class Index extends WebSocketController
{
    /**
     * 健康检测地址
     * @return array
     */
    public function healthy()
    {
        return [
            'code' => 4,
        ];
    }

    public function index()
    {
        $this->responseCode(201);
        return [
            'data' => $this->request->getData(),
            'msg' => __CLASS__,
            'module_name' => $this->request->getModuleName()
        ];
    }

    public function websocket()
    {
        $data = [
            'code' => $this->request->post('code'),
            'user_id' => $this->request->post('user_id'),
            'content' => $this->request->post('content'),
        ];
        if ($this->request->post('code') == 3) {
            $rpc = new ManagerRpc();
            $rpc->push($data);
            return ['code' => 4];
        }
        if ($this->request->post('code') == 1) {
            $userSession = new UserSessionModel();
            $config = ManagerServer::getInstance()->getServerConfig();
            $userSession->where([
                'login_server' => ManagerServer::getInstance()->getUniquelyIdentifies().'_'.$config['host'].':'.$config['port'],
                'fd' => $this->getFd()
            ])->update([
                'username' => $this->request->post('user_id')
            ]);
            $user = new UserModel();
            $info = $user->where([
                'username' => $this->request->post('user_id')
            ])->find();
            if(empty($info)){
                $user->renew()->insert([
                    'username' => $this->request->post('user_id'),
                    'heart' => time()
                ]);
            }
        }
        if($this->request->post('code') == 4){
            $user = new UserModel();
            $res = $user->where([
                'username' => $this->request->post('user_id')
            ])->update([
                'heart' => time()
            ]);
            $data['user_list'] = [];
            $list = $user->renew()->findAll();
            foreach ($list as &$val){
                array_push($data['user_list'], [
                    'is_online' => $val['heart'] > time()-120,
                    'user_id' => $val['username']
                ]);
            }
        }
        return $data;

    }

    public function publish()
    {
        $data = [
            'code' => $this->request->post('code'),
            'user_id' => $this->request->post('user_id'),
            'content' => $this->request->post('content'),
        ];
        $userSession = new UserSessionModel();
        $config = ManagerServer::getInstance()->getServerConfig();
        $list = $userSession->where([
            'login_server' => ManagerServer::getInstance()->getUniquelyIdentifies().'_'.$config['host'].':'.$config['port'],
            'fd' => ['neq', '']
        ])->findAll();
        if(!empty($list)){
            $fds = ManagerServer::getInstance()->getMasterServer()->fds;
            foreach ($list as $val){
                if(isset($fds[$val['fd']])
                    && $this->request->post('user_id') != $val['username']
                ){
                   ManagerServer::getInstance()->getServer()->push(intval($val['fd']), json_encode($data, JSON_UNESCAPED_UNICODE));
                }
            }
        }
        return ['code' => 200];
    }

    public function index1()
    {
        $this->responseCode(201);
        return [
            'msg' => 'success',
            'data' => $this->request->getData(),
        ];
    }

    public function hello()
    {
        return '这是个界面';
    }


}