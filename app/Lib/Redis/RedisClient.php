<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Lib\Redis;

use App\Config\ConfigAdapter;
use App\Exceptions\ResourceConnectException;
use App\Utils\ResponseUtil;


class RedisClient
{
    private static $instance;

    private $asyncRedis;

    /**
     * RedisClient constructor.
     *
     * @throws \App\Exceptions\ResourceConnectException
     */
    private function __construct()
    {
        $config = ConfigAdapter::get(ConfigAdapter::CONFIG_REDIS_KEY);

        $this->asyncRedis = new \Swoole\Coroutine\Redis();

        if (! $this->asyncRedis->connect($config['host'], $config['port'])) {
            throw new ResourceConnectException('Redis connect failed', ResponseUtil::HTTP_ERROR);
        }
    }

    /**
     * forbid clone
     */
    public function __clone()
    {
    }

    /**
     */
    public static function instance()
    {
        if (self::$instance == null) {
            try {
                self::$instance = new self();
            } catch (ResourceConnectException $e) {
            }
        }

        return self::$instance;
    }

    public function doSomething($action, $arguments)
    {
        return $this->asyncRedis->$action(...$arguments);
    }

    /**
     * destroy
     */
    public function __destruct()
    {
        // release redis resource
        if ($this->asyncRedis != null) {
            $this->asyncRedis->close();
        }
    }
}