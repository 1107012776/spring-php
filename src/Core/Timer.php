<?php

namespace SpringPHP\Core;
abstract class Timer
{
    public $data;
    public $ms = 0;  //毫秒
    protected $response;

    public function __construct($params = [])
    {
        foreach ($params as $key => $val){
            if(property_exists($this, $key)){
                $this->$key = $val;
            }
        }
    }

    public function run(){
        try{
            $this->before();
            $this->exec();
            $this->after();
        }catch (\Exception $e){
            return $this->onException($e);
        }
        return $this->response;
    }

    abstract protected function before();

    abstract protected function exec();

    abstract protected function after();

    abstract protected function onException(\Throwable $throwable, $arg = null);

}