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

### 查看进程
```bash
php spring-php process
```

```bash
root     29336  0.0  0.2 438400 10156 pts/3    S    15:56   0:00 spring-php
root     29337  0.0  0.2 632012 11392 pts/3    Sl   15:56   0:00  \_ spring-php.Manager
root     29352  0.0  0.2 487876 10676 pts/3    S    15:56   0:00  |       \_ spring-php.task.2 pid=29352
root     29354  0.0  0.2 487876 10676 pts/3    S    15:56   0:00  |       \_ spring-php.task.3 pid=29354
root     29355  0.0  0.2 487804 11108 pts/3    S    15:56   0:00  |       \_ spring-php.worker.0 listen:0.0.0.0:7999
root     29356  0.0  0.2 487876 10872 pts/3    S    15:56   0:00  |       \_ spring-php.worker.1 listen:0.0.0.0:7999
root     29363  0.0  0.2 489928 10728 pts/3    S    15:56   0:00  |       \_ spring-php RenderWorker unix worker pid=29363
root     29364  0.0  0.2 489928 10728 pts/3    S    15:56   0:00  |       \_ spring-php RenderWorker unix worker pid=29364
root     29365  0.0  0.2 489928 10728 pts/3    S    15:56   0:00  |       \_ spring-php RenderWorker unix worker pid=29365
root     29368  0.0  0.2 487876 10488 pts/3    S    15:56   0:00  |       \_ spring-php.Crontab worker pid=29368
root     29338  0.0  0.2 632012 11388 pts/3    Sl   15:56   0:00  \_ spring-php.Manager
root     29349  0.0  0.2 487876 10676 pts/3    S    15:56   0:00  |       \_ spring-php.task.2 pid=29349
root     29350  0.0  0.2 487876 10676 pts/3    S    15:56   0:00  |       \_ spring-php.task.3 pid=29350
root     29351  0.0  0.2 487812 11112 pts/3    S    15:56   0:00  |       \_ spring-php.worker.0 listen:0.0.0.0:8098
root     29353  0.0  0.2 487876 10872 pts/3    S    15:56   0:00  |       \_ spring-php.worker.1 listen:0.0.0.0:8098
root     29357  0.0  0.2 487876 10496 pts/3    S    15:56   0:00  |       \_ spring-php RenderWorker worker pid=29357 listen:0.0.0.0:8099
root     29369  0.0  0.2 487876 10512 pts/3    S    15:56   0:00  |       \_ spring-php RenderWorker worker pid=29369 listen:0.0.0.0:8100
root     29339  0.0  0.2 632012 11388 pts/3    Sl   15:56   0:00  \_ spring-php.Manager
root     29358  0.0  0.2 487876 10660 pts/3    S    15:56   0:00          \_ spring-php.task.2 pid=29358
root     29359  0.0  0.2 487876 10660 pts/3    S    15:56   0:00          \_ spring-php.task.3 pid=29359
root     29360  0.0  0.2 487812 11056 pts/3    S    15:56   0:00          \_ spring-php.worker.0 listen:0.0.0.0:8297
root     29362  0.0  0.2 487876 10856 pts/3    S    15:56   0:00          \_ spring-php.worker.1 listen:0.0.0.0:8297
root     29366  0.0  0.2 487876 10476 pts/3    S    15:56   0:00          \_ spring-php RenderWorker worker pid=29366 listen:0.0.0.0:8298
root     29367  0.0  0.2 487876 10476 pts/3    S    15:56   0:00          \_ spring-php RenderWorker worker pid=29367 listen:0.0.0.0:8299
```


  

