<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Lib\MySql;

use App\Config\ConfigAdapter;
use App\Exceptions\MySqlQueryException;
use App\Exceptions\ResourceConnectException;
use App\Utils\ResponseUtil;

class MySqlClient
{
    private static $instance;

    private $mysql;

    /**
     * MySqlClient constructor.
     *
     * @throws \App\Exceptions\ResourceConnectException
     */
    private function __construct()
    {
        $config = ConfigAdapter::get(ConfigAdapter::CONFIG_MYSQL_KEY);

        $this->mysql = new \Swoole\Coroutine\MySQL();
        if (! $this->mysql->connect($config)) {
            throw new ResourceConnectException('MySql connect failed', ResponseUtil::HTTP_ERROR);
        }
    }

    /**
     * @return \App\Lib\MySql\MySqlClient
     * @throws \App\Exceptions\ResourceConnectException
     */
    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * query data set using specified condition and do inserting, deleting or modify operation
     *
     * @param $query
     * @return mixed
     * @throws \App\Exceptions\MySqlQueryException
     */
    public function query($query)
    {
        $result = $this->mysql->query($query);

        if ($result === false) {
            throw new MySqlQueryException('查询失败');
        }

        return $result;
    }

    /**
     * forbid clone
     */
    public function __clone()
    {
    }

    /**
     * destroy
     */
    public function __destruct()
    {
        if ($this->mysql != null) {
            $this->mysql->close();
        }
    }
}