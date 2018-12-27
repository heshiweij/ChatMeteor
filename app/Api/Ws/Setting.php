<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Api\Ws;

use App\Exceptions\BadRequestException;
use App\Lib\Redis\RedisClient;
use App\Lib\Redis\RedisKeys;
use App\Utils\ResponseUtil;

class Setting
{
    /**
     * bind user id with fd
     *
     * @param $args
     * @throws \App\Exceptions\BadRequestException
     */
    public function bind($args)
    {
        // 检查参数
        if (! isset($args['user_id']) || empty($args['user_id'])) {
            throw new BadRequestException('Argument is not valid: miss user_id', ResponseUtil::HTTP_BAD_REQUEST);
        }

        $fd     = $_SERVER['frame']->fd;
        $userId = $args['user_id'];

        // 正向
        RedisClient::instance()->doSomething('hset', [RedisKeys::USER_ONLINE_LIST, $fd, $userId]);

        // 反向
        RedisClient::instance()->doSomething('hset', [RedisKeys::USER_ONLINE_LIST_REVERSE, $userId, $fd]);
    }
}