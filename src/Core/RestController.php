<?php
/**
 * SpringPHP file.
 * @author linyushan  <1107012776@qq.com>
 * @link https://www.developzhe.com/
 * @package https://github.com/1107012776/spring-php
 * @license https://github.com/1107012776/spring-php/blob/main/LICENSE
 */
namespace SpringPHP\Core;

class RestController extends Controller
{
    public function beforeAction($action = '')
    {
        $res = parent::beforeAction($action);
        if (empty($res)) {
            return $res;
        }
        $verbs = $this->verbs();
        if (in_array($action, array_keys($verbs))) {
            if (!in_array($this->request->method(), $verbs[$action])) {
                return false;
            }
        }
        return true;
    }

    /**
     * 定义请求方式
     * @return array
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD', 'POST'],
            'view' => ['GET', 'HEAD', 'POST'],
            'create' => ['POST', 'POST'],
            'update' => ['PUT', 'PATCH', 'GET'],
            'delete' => ['DELETE', 'POST'],
        ];
    }
}