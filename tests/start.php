<?php
$file = null;
foreach ([ __DIR__ . '/../../../autoload.php', __DIR__ . '/../../vendor/autoload.php',__DIR__ . '/../vendor/autoload.php' ] as $file) {
    if (file_exists($file)) {
        require $file;
        break;
    }
}
if(!file_exists($file)){
    die("include composer autoload.php fail\n");
}

