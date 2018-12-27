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

class Single
{
    /**
     * send single message
     *
     * @param $args
     * @throws \App\Exceptions\BadRequestException
     */
    public function send($args)
    {
        // 检查参数
        if (! isset($args['to_user']) || empty($args['to_user'])) {
            throw new BadRequestException('Argument is not valid: miss to_user', ResponseUtil::HTTP_BAD_REQUEST);
        }

        if (! isset($args['message']) || empty($args['message'])) {
            throw new BadRequestException('Argument is not valid: miss message', ResponseUtil::HTTP_BAD_REQUEST);
        }

        $server = $_SERVER['server'];

        $fd = $_SERVER['frame']->fd;

        $toUserId = $args['to_user'];

        $fromUserId = RedisClient::instance()->doSomething('hget', [RedisKeys::USER_ONLINE_LIST, $fd]);

        $fd = RedisClient::instance()->doSomething('hget', [RedisKeys::USER_ONLINE_LIST_REVERSE, $toUserId]);

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