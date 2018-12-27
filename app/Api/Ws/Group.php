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

class Group
{
    /**
     * send group message
     *
     * @param $args
     * @throws \App\Exceptions\BadRequestException
     */
    public function send($args)
    {
        // 检查参数
        if (! isset($args['group_id']) || empty($args['group_id'])) {
            throw new BadRequestException('Argument is not valid: miss group_id', ResponseUtil::HTTP_BAD_REQUEST);
        }

        if (! isset($args['message']) || empty($args['message'])) {
            throw new BadRequestException('Argument is not valid: miss message', ResponseUtil::HTTP_BAD_REQUEST);
        }

        // 检查群组是否存在
        $groupId = $args['group_id'];

        $exists = RedisClient::instance()->doSomething('exists', [RedisKeys::GROUP_PREFIX.$groupId,]);

        if (! $exists) {
            throw new BadRequestException('Group is not exists', ResponseUtil::HTTP_ERROR);
        }

        $users = RedisClient::instance()->doSomething('lrange',
            [RedisKeys::GROUP_USER_LIST_PREFIX.$groupId, 0, 100000]);

        $server = $_SERVER['server'];

        $fromFd = $_SERVER['frame']->fd;

        $fromUserId = $this->getUserIdByFd($fromFd);

        // 检查自己是否在群组里
        if (! in_array($fromUserId, $users)) {
            throw new BadRequestException('Your are not in this group', ResponseUtil::HTTP_ERROR);
        }

        foreach ($users as $userId) {

            $fd = $this->getFdByUserId($userId);
            //var_dump("==== fd ==> ", $fd);
            $server->push($fd, json_encode([
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

    private function getUserIdByFd($fd)
    {
        $userId = RedisClient::instance()->doSomething('hget', [RedisKeys::USER_ONLINE_LIST, $fd]);

        return intval($userId);
    }

    private function getFdByUserId($userId)
    {
        //var_dump(" ===> userid ", $userId);
        $fd = RedisClient::instance()->doSomething('hget', [RedisKeys::USER_ONLINE_LIST_REVERSE, $userId]);

        return intval($fd);
    }
}