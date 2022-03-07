<?php

namespace App\Config;


use  \PhpShardingPdo\Core\ShardingTableRuleConfig;
use  \PhpShardingPdo\Core\InlineShardingStrategyConfiguration;
use  \PhpShardingPdo\Core\ShardingRuleConfiguration;
use PhpShardingPdo\Core\SPDO;
use  \PhpShardingPdo\Inter\ShardingInitConfigInter;
use SpringPHP\Core\SpringContext;

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/24
 * Time: 18:48
 */
class ShardingInitConfig extends ShardingInitConfigInter
{
    /**
     * 获取分库分表map各个数据的实例
     * return
     */
    protected function getDataSourceMap()
    {
        return [
            'db0' => self::initDataResurce1(),
        ];
    }

    protected function getShardingRuleConfiguration()
    {
        $shardingRuleConfig = new ShardingRuleConfiguration();

        //server_manager
        $tableRule = new ShardingTableRuleConfig();
        $tableRule->setLogicTable('server_manager');
        $tableRule->setDatabaseShardingStrategyConfig(
            new InlineShardingStrategyConfiguration('db', [], function ($condtion) {
                return '0';
            }));
        $tableRule->setTableShardingStrategyConfig(
            new InlineShardingStrategyConfiguration('server_manager', [], function ($condtion) {
                return '';
            }));
        $shardingRuleConfig->add($tableRule);

        //user
        $tableRule = new ShardingTableRuleConfig();
        $tableRule->setLogicTable('user');
        $tableRule->setDatabaseShardingStrategyConfig(
            new InlineShardingStrategyConfiguration('db', [], function ($condtion) {
                return '0';
            }));
        $tableRule->setTableShardingStrategyConfig(
            new InlineShardingStrategyConfiguration('user', [], function ($condtion) {
                return '';
            }));

        $shardingRuleConfig->add($tableRule);

        //user_session
        $tableRule = new ShardingTableRuleConfig();
        $tableRule->setLogicTable('user_session');
        $tableRule->setDatabaseShardingStrategyConfig(
            new InlineShardingStrategyConfiguration('db', [], function ($condtion) {
                return '0';
            }));
        $tableRule->setTableShardingStrategyConfig(
            new InlineShardingStrategyConfiguration('user_session', [], function ($condtion) {
                return '';
            }));
        $shardingRuleConfig->add($tableRule);
        return $shardingRuleConfig;
    }


    protected static function initDataResurce1()
    {
        $dbms = 'mysql';     //数据库类型
        $host = SpringContext::config('local.shardingPdo.database.0.host', 'localhost'); //数据库主机名
        $dbName = SpringContext::config('local.shardingPdo.database.0.name', 'spring-php-imi');    //使用的数据库
        $user = SpringContext::config('local.shardingPdo.database.0.username', 'root');      //数据库连接用户名
        $pass = SpringContext::config('local.shardingPdo.database.0.password', '');;          //对应的密码
        $port = SpringContext::config('local.shardingPdo.database.0.port', 3306);;          //对应的端口
        $charset = SpringContext::config('local.shardingPdo.database.0.charset', 'utf8mb4');;          //对应的端口
        $dsn = "$dbms:host=$host;dbname=$dbName;port=$port;charset=$charset";
        try {
            return self::connect($dsn, $user, $pass);
        } catch (\PDOException $e) {
            \Swoole\Event::exit();
        }
    }


    protected static function connect($dsn, $user, $pass, $option = [])
    {
        $dbh = new SPDO($dsn, $user, $pass, $option);
        $dbh->query('set names utf8mb4;');
        return $dbh;
    }

    /**
     * 获取sql执行xa日志路径，当xa提交失败的时候会出现该日志
     * @return string
     */
    protected function getExecTransactionSqlLogFilePath()
    {
        $runtime_path = SpringContext::config('settings.runtime_path');
        $execTransactionSqlLogFilePath = SpringContext::config('local.shardingPdo.execTransactionSqlLogFilePath', $runtime_path . '/execXaSqlLogFilePath.log');
        $dir = dirname($execTransactionSqlLogFilePath);
        if(!file_exists($dir)){
            mkdir($dir, 0777, true);
        }
        return $execTransactionSqlLogFilePath;
    }
}