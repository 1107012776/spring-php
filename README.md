# SpringPHP - 高性能 Swoole 框架
SpringPHP是一款基于Swoole的高性能框架

# 说明
当前属于测试阶段，请勿用于生产环境

# 安装
php composer.phar create-project spring-php/demo [目录名称] [版本]
#### 如下
```bash
php composer.phar create-project spring-php/demo demo dev-master
```

# 示例

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

### 查看进程
```bash
php spring-php process
```

```bash
 spring-php
  \_ spring-php.Manager
  |       \_ spring-php.task.2 pid=29352
  |       \_ spring-php.task.3 pid=29354
  |       \_ spring-php.worker.0 listen:0.0.0.0:7999
  |       \_ spring-php.worker.1 listen:0.0.0.0:7999
  |       \_ spring-php RenderWorker unix worker pid=29363
  |       \_ spring-php RenderWorker unix worker pid=29364
  |       \_ spring-php RenderWorker unix worker pid=29365
  |       \_ spring-php.Crontab worker pid=29368
  \_ spring-php.Manager
  |       \_ spring-php.task.2 pid=29349
  |       \_ spring-php.task.3 pid=29350
  |       \_ spring-php.worker.0 listen:0.0.0.0:8098
  |       \_ spring-php.worker.1 listen:0.0.0.0:8098
  |       \_ spring-php RenderWorker worker pid=29357 listen:0.0.0.0:8099
  |       \_ spring-php RenderWorker worker pid=29369 listen:0.0.0.0:8100
  \_ spring-php.Manager
          \_ spring-php.task.2 pid=29358
          \_ spring-php.task.3 pid=29359
          \_ spring-php.worker.0 listen:0.0.0.0:8297
          \_ spring-php.worker.1 listen:0.0.0.0:8297
          \_ spring-php RenderWorker worker pid=29366 listen:0.0.0.0:8298
          \_ spring-php RenderWorker worker pid=29367 listen:0.0.0.0:8299
```


# Page visitor counter

![visitor counter](https://profile-counter.glitch.me/1107012776_spring-php/count.svg)

