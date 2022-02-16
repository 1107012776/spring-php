<?php

namespace App\Template;


use SpringPHP\Inter\RenderInter;

class Smarty implements RenderInter
{

    private $smarty;

    function __construct()
    {
        $temp = sys_get_temp_dir();
        $this->smarty = new \Smarty();
        $this->smarty->setLeftDelimiter('{{');
        $this->smarty->setRightDelimiter('}}');
        $this->smarty->setTemplateDir(SPRINGPHP_ROOT . '/App/Views/');
        $this->smarty->setCacheDir("{$temp}/smarty/cache/");
        $this->smarty->setCompileDir("{$temp}/smarty/compile/");
    }


    public function assign($key, $item)
    {
        $this->smarty->assign($key, $item);
    }

    /**
     *  这是注册的语句,第一个参数"function"是固定写法,第二个参数是可以重命名到模板中引用的函数名,第三个参数是下面自己写的函数名称
     * @param $type
     * @param $name
     * @param $callback
     * @param bool $cacheable
     * @param null $cache_attr
     * @return \Smarty|\Smarty_Internal_Template
     * @throws \SmartyException
     */
    public function registerPlugin($type, $name, $callback, $cacheable = true, $cache_attr = null)
    {
        return $this->smarty->registerPlugin($type, $name, $callback, $cacheable, $cache_attr);
    }

    public function render(string $template, ?array $data = [], ?array $options = []): ?string
    {
        if (strpos($template, '.phtml') === false) {
            $template .= '.phtml';
        }
        foreach ($data as $key => $item) {
            $this->smarty->assign($key, $item);
        }
        return $this->smarty->fetch($template, $cache_id = null, $compile_id = null, $parent = null, $display = false,
            $merge_tpl_vars = true, $no_output_filter = false);
    }

    public function afterRender(?string $result, string $template, array $data = [], array $options = [])
    {

    }

    public function onException(\Throwable $throwable, $arg): string
    {
        $msg = "{$throwable->getMessage()} at file:{$throwable->getFile()} line:{$throwable->getLine()}";
        trigger_error($msg);
        return $msg;
    }
}