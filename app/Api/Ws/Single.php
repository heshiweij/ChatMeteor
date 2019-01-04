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

class Single
{
    use Validation;

    /**
     * send single message
     *
     * @param $args
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function send($args)
    {
        // 检查参数
        $this->validateArguments($args, 'to_user');
        $this->validateArguments($args, 'message');

        $server = $_SERVER['server'];

        $fd = $_SERVER['frame']->fd;

        $toUserId = $args['to_user'];

        $fromUserId = get_value_hash_from_redis(RedisKeys::USER_ONLINE_LIST, $fd);

        $fd = get_value_hash_from_redis(RedisKeys::USER_ONLINE_LIST_REVERSE, $toUserId);

        if (intval($fd) > 0) {
            $server->push($fd, json_encode([
                'type'     => 'ws',
                'category' => 'single',
                'args'     => [
                    'from_user' => $fromUserId,
                    'message'   => $args['message'],
                ],
            ]));
        }
    }
}