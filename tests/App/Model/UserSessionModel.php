<?php

namespace App\Model;


use App\Config\ShardingInitConfig;


use PhpShardingPdo\Core\Model;

Class UserSessionModel extends Model
{
    protected $tableName = 'user_session';
    protected $tableNameIndexConfig = [];
    protected $shardingInitConfigClass = ShardingInitConfig::class;
}