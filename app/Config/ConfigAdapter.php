<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Config;

use App\Lib\Table\SwooleTableClient;

class ConfigAdapter
{
    /**
     * server config key in config.yaml
     */
    const CONFIG_SERVER_KEY = 'server';

    /**
     * redis config key in config.yaml
     */
    const CONFIG_REDIS_KEY = 'redis';

    /**
     * mysql config key in config.yaml
     */
    const CONFIG_MYSQL_KEY = 'mysql';

    public static function get($key)
    {
        $config = SwooleTableClient::instance()->get($key);

        return json_decode($config, true);
    }

    public static function isDebug()
    {
        return SwooleTableClient::instance()->get('debug');
    }
}