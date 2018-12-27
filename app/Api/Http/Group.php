<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Api\Http;

use App\Exceptions\BadRequestException;
use App\Lib\Redis\RedisClient;
use App\Lib\Redis\RedisKeys;
use App\Traits\Helpers;
use App\Utils\ResponseUtil;

class Group
{
    use Helpers;

    /**
     * 获取群组列表
     *
     * @param $args
     * @return
     */
    public function list($args)
    {
        $groups = RedisClient::instance()->doSomething('keys', [
            RedisKeys::GROUP_PREFIX.str_repeat('?', 6),
        ]);

        $result = [];

        foreach ($groups as $group) {

            $isActive = $args['is_active'];

            if ($isActive) {
                $users = RedisClient::instance()->doSomething('hget', [
                    $group,
                    'users',
                ]);

                if (intval($users) < 2) {
                    continue;
                }
            }

            $result[] = [
                'group_id'   => RedisClient::instance()->doSomething('hget', [
                    $group,
                    'group_id',
                ]),
                'group_name' => RedisClient::instance()->doSomething('hget', [
                    $group,
                    'group_name',
                ]),
                'users'      => RedisClient::instance()->doSomething('hget', [
                    $group,
                    'users',
                ]),
                'updated_at' => RedisClient::instance()->doSomething('hget', [
                    $group,
                    'updated_at',
                ]),
            ];
        }

        return ResponseUtil::success('获取群组列表成功', $result);
    }

    /**
     * 创建群组
     *
     * @param $args
     * @throws \App\Exceptions\BadRequestException
     */
    public function create($args)
    {
        //RedisClient::instance()->doSomething('lpush', ['fdfs', 1,2,3,4]);
        //
        //return;
        // 检查参数
        if (! isset($args['group_name']) || empty($args['group_name'])) {
            throw new BadRequestException('Argument is not valid: miss group_name', ResponseUtil::HTTP_BAD_REQUEST);
        }

        if (! isset($args['users']) || empty($args['users'])) {
            throw new BadRequestException('Argument is not valid: miss users', ResponseUtil::HTTP_BAD_REQUEST);
        }

        $groupName = $args['group_name'];
        $users     = $args['users'];

        // 检查群组是否存在
        $groupId = substr(md5($groupName), 0, 6);

        $exists = RedisClient::instance()->doSomething('exists', [RedisKeys::GROUP_PREFIX.$groupId,]);

        if ($exists) {
            throw new BadRequestException('Group has already exists', ResponseUtil::HTTP_ERROR);
        }

        if (! is_array($users)) {
            throw new BadRequestException('Argument is not valid: users is not array', ResponseUtil::HTTP_BAD_REQUEST);
        }

        RedisClient::instance()->doSomething('hset', [RedisKeys::GROUP_PREFIX.$groupId, 'group_id', $groupId]);
        RedisClient::instance()->doSomething('hset', [RedisKeys::GROUP_PREFIX.$groupId, 'group_name', $groupName]);
        RedisClient::instance()->doSomething('hset', [RedisKeys::GROUP_PREFIX.$groupId, 'users', count($users)]);

        $params = [RedisKeys::GROUP_USER_LIST_PREFIX.$groupId];
        $params = array_merge($params, $users);
        RedisClient::instance()->doSomething('lpush', $params);

        return ResponseUtil::success('创建群组成功', [
            'group_id'   => $groupId,
            'group_name' => $groupName,
        ]);
    }

    /**
     * 加入群组
     *
     * @param $args
     * @throws \App\Exceptions\BadRequestException
     */
    public function append($args)
    {
        // 检查参数
        if (! isset($args['group_id']) || empty($args['group_id'])) {
            throw new BadRequestException('Argument is not valid: miss group_id', ResponseUtil::HTTP_BAD_REQUEST);
        }

        if (! isset($args['users']) || empty($args['users'])) {
            throw new BadRequestException('Argument is not valid: miss users', ResponseUtil::HTTP_BAD_REQUEST);
        }

        // 检查群组是否存在
        $groupId = $args['group_id'];

        $exists = RedisClient::instance()->doSomething('exists', [RedisKeys::GROUP_PREFIX.$groupId,]);

        if (! $exists) {
            throw new BadRequestException('Group is not exists', ResponseUtil::HTTP_ERROR);
        }

        $users = $args['users'];

        // 增加群人数
        RedisClient::instance()->doSomething('hincrby', [RedisKeys::GROUP_PREFIX.$groupId, 'users', count($users)]);

        // 增加群成员列表

        $members = RedisClient::instance()->doSomething('lrange',
            [RedisKeys::GROUP_USER_LIST_PREFIX.$groupId, 0, 100000]);

        if (empty($members)) {
            $members = [];
        }

        $intersect = [];

        foreach ($users as $user) {
            if (! in_array($user, $members)) {
                $intersect[] = $user;
            }
        }

        $params = [RedisKeys::GROUP_USER_LIST_PREFIX.$groupId];
        $params = array_merge($params, $intersect);
        RedisClient::instance()->doSomething('lpush', $params);

        return ResponseUtil::success('加入群组成功', $intersect);
    }

    /**
     * 获取指定群组的用户列表
     *
     * @param $args
     * @throws \App\Exceptions\BadRequestException
     */
    public function users($args)
    {
        // 检查参数
        if (! isset($args['group_id']) || empty($args['group_id'])) {
            throw new BadRequestException('Argument is not valid: miss group_id', ResponseUtil::HTTP_BAD_REQUEST);
        }

        // 检查群组是否存在
        $groupId = $args['group_id'];

        $exists = RedisClient::instance()->doSomething('exists', [RedisKeys::GROUP_PREFIX.$groupId,]);

        if (! $exists) {
            throw new BadRequestException('Group is not exists', ResponseUtil::HTTP_ERROR);
        }

        $members = RedisClient::instance()->doSomething('lrange',
            [RedisKeys::GROUP_USER_LIST_PREFIX.$groupId, 0, 100000]);

        // 去除重复
        $members = array_unique($members);

        return ResponseUtil::success('获取群组用户列表成功', array_values($members));
    }
}