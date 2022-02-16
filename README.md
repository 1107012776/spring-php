# SpringPHP - 高性能 Swoole 框架
SpringPHP是一款基于Swoole的高性能框架

# 说明
当前属于测试阶段，请勿用于生产环境

# 安装

```
composer require lys/spring-php

cp vendor/lys/spring-php/tests/spring-php ./spring-php    //复制启动脚本spring-php

php spring-php installDemo  //安装demo案例，可自由删减不需要的部分

```

# 示例（tests目录）

### 启动
```bash
php spring-php start   //守护模式需要在start后面加 -d
```
```

////////////////////////////////////////////////////////////////////
//      ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^        //
//                          SpringPHP                             //
//            https://github.com/1107012776/spring-php            //
////////////////////////////////////////////////////////////////////

```

### 关闭
```bash
php spring-php stop
```

### 重启worker
```bash
php spring-php reload
```


  

