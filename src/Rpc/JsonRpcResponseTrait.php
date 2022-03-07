<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */

namespace SpringPHP\Rpc;

trait JsonRpcResponseTrait
{
    public function responseJsonRpc($result)
    {
        if (empty($result['success'])) {
            return $this->responseErrorJsonRpc($result);
        }
        return [
            'jsonrpc' => '2.0',
            'result' => $result,
            'id' => method_exists($this->request, 'getJsonRpcId') ? $this->request->getJsonRpcId() : 0,
        ];
    }

    public function responseErrorJsonRpc($result)
    {
        return [
            'jsonrpc' => '2.0',
            'error' => $result,
            'id' => method_exists($this->request, 'getJsonRpcId') ? $this->request->getJsonRpcId() : 0,
        ];
    }
}