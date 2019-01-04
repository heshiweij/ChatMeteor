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

class Group
{
    use Validation;

    /**
     * send group message
     *
     * @param $args
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function send($args)
    {
        // 检查参数
        $this->validateArguments($args, 'group_id');
        $this->validateArguments($args, 'message');

        // 检查群组是否存在
        $groupId = $args['group_id'];

        if (! exists_key_redis(RedisKeys::GROUP_PREFIX.$groupId)) {
            throw new BadRequestException('Group is not exists', ResponseUtil::HTTP_ERROR);
        }

        $users = RedisClient::instance()->doSomething('lrange',
            [RedisKeys::GROUP_USER_LIST_PREFIX.$groupId, 0, 100000]);

        $server = $_SERVER['server'];

        $fromFd = $_SERVER['frame']->fd;

        $fromUserId = get_value_hash_from_redis(RedisKeys::USER_ONLINE_LIST, $fromFd);

        // 检查自己是否在群组里
        if (! in_array($fromUserId, $users)) {
            throw new BadRequestException('Your are not in this group', ResponseUtil::HTTP_ERROR);
        }

        foreach ($users as $userId) {

            $fd = get_value_hash_from_redis(RedisKeys::USER_ONLINE_LIST_REVERSE, $userId);

            $server->push(intval($fd), json_encode([
                'type'     => 'ws',
                'category' => 'group',
                'args'     => [
                    'from_group' => $groupId,
                    'from_user'  => $fromUserId,
                    'message'    => $args['message'],
                ],
            ]));
        }
    }
}