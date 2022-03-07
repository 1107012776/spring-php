<?php

namespace App\Model;


use App\Config\ShardingInitConfig;


use PhpShardingPdo\Core\Model;

Class UserModel extends Model
{
    protected $tableName = 'user';
    protected $tableNameIndexConfig = [];
    protected $shardingInitConfigClass = ShardingInitConfig::class;
}