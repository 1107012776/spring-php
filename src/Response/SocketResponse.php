<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Response;

use SpringPHP\Request\RequestSocket;

/**
 * Created by PhpStorm.
 * User: 11070
 * Date: 2022/2/20
 * Time: 21:22
 */
class SocketResponse
{
    use \SpringPHP\Rpc\JsonRpcResponseTrait;
    protected $http_code = 200;
    protected $reason;
    protected $data;
    /**
     * @var RequestSocket
     */
    protected $request;

    public function setRequest($request)
    {
        $this->request = $request;
    }


    public function setStatusCode($http_code = 200, $reason = null)
    {
        $this->http_code = $http_code;
        $this->reason = $reason;
    }

    /**
     * @return int
     */
    public function getHttpCode(): int
    {
        return $this->http_code;
    }

    /**
     * @return mixed
     */
    public function getReason()
    {
        return $this->reason;
    }


    public function response($result)
    {
        if (is_array($result)) {
            empty($result['code']) && $result['code'] = $this->getHttpCode();
        }
        if ($this->request->isJsonrpc()) {
            return $this->responseJsonRpc($result);
        }
        return $result;
    }

}