<?php

namespace App\Model;


use App\Config\ShardingInitConfig;


use PhpShardingPdo\Core\Model;

Class ServerManagerModel extends Model
{
    protected $tableName = 'server_manager';
    protected $tableNameIndexConfig = [];
    protected $shardingInitConfigClass = ShardingInitConfig::class;
}