<?php

namespace SpringPHP\Test\unit;

use PHPUnit\Framework\TestCase;

$file_load_path = __DIR__ . '/../../../autoload.php';
if (file_exists($file_load_path)) {
    include $file_load_path;
} else {
    $vendor = __DIR__ . '/../vendor/autoload.php';
    include $vendor;
}

use Yurun\Util\HttpRequest;

class ApiTest extends TestCase
{


    protected function getHttp()
    {
        $http = new HttpRequest;
        // 设置 Header 4 种方法
        $http->header('aaa', 'value1')
            ->headers([
                'bbb' => 'value2',
                'ccc' => 'value3',
            ])
            ->rawHeader('ddd:value4')
            ->rawHeaders([
                'eee:value5',
                'fff:value6',
            ]);
        return $http;
    }

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * php vendor/bin/phpunit tests/unit/ApiTest.php --filter testRequest
     * @throws
     */
    public function testRequest()
    {
        // 请求
        $response = $this->getHttp()->ua('SpringPHPTest')
            ->get('http://127.0.0.1:7999/Index/index');
        $this->dump($response);
        $response = $this->getHttp()->ua('SpringPHPTest')
            ->get('http://127.0.0.1:8098/Index/index');
        $this->dump($response);
        $response = $this->getHttp()->ua('SpringPHPTest')
            ->get('http://127.0.0.1:8297/Index/index');
        $this->dump($response);
    }

    public function testRestFul()
    {
        $this->testGet();
        $this->testPost();
        $this->testPut();
        $this->testHead();
        $this->testDelete();
    }

    public function testGet()
    {
        $response = $this->getHttp()->ua('SpringPHPTest')
            ->get('http://127.0.0.1:8098/Index/get?id=1');
        $this->dump($response);
    }

    public function testPost()
    {
        $response = $this->getHttp()->ua('SpringPHPTest')
            ->post('http://127.0.0.1:8098/Index/post', [
                'id' => 2
            ]);
        $this->dump($response);
    }

    public function testPut()
    {
        $response = $this->getHttp()->ua('SpringPHPTest')
            ->put('http://127.0.0.1:8098/Index/put', [
                'id' => 3
            ]);
        $this->dump($response);
    }

    public function testHead()
    {
        $response = $this->getHttp()->ua('SpringPHPTest')
            ->head('http://127.0.0.1:8098/Index/head', [
                'id' => 4
            ]);
        $this->dump($response);
    }

    public function testDelete()
    {
        $response = $this->getHttp()->ua('SpringPHPTest')
            ->delete('http://127.0.0.1:8098/Index/delete', [
                'id' => 5
            ]);
        $this->dump($response);
    }

    protected function dump(\Yurun\Util\YurunHttp\Http\Response $response)
    {
        echo $response->getStatusCode() . PHP_EOL;
        $body = $response->getBody();
        echo $body . PHP_EOL;
        $this->assertEquals(!empty($body), true);
    }
}