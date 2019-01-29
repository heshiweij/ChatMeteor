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
use App\Traits\Validation;
use App\Utils\ResponseUtil;

class Setting
{
    use Validation;

    /**
     * bind user id with fd
     *
     * @param $args
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function bind($args)
    {
        // 检查参数
        $this->validateArguments($args, 'user_id');

        $fd     = $_SERVER['frame']->fd;
        $userId = $args['user_id'];

        // 清除之前绑定的数据
        $originUserId = get_value_hash_from_redis(RedisKeys::USER_ONLINE_LIST, $fd);

        if (! empty($originUserId)) {
            delete_key_hash_in_redis(RedisKeys::USER_ONLINE_LIST_REVERSE, $originUserId);
        }

        // 正向
        set_value_hash_to_redis(RedisKeys::USER_ONLINE_LIST, $fd, $userId);

        // 反向
        set_value_hash_to_redis(RedisKeys::USER_ONLINE_LIST_REVERSE, $userId, $fd);
    }
}