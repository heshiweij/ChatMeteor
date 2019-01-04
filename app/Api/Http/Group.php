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
use App\Traits\Validation;
use App\Utils\ResponseUtil;

class Group
{
    use Validation;

    /**
     * 获取群组列表
     *
     * @param $args
     * @return
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function list($args)
    {
        $groups = get_keys_from_redis(RedisKeys::GROUP_PREFIX.str_repeat('?', 6));

        $result = [];

        foreach ($groups as $group) {

            $isActive = $args['is_active'];

            if ($isActive) {
                $users = get_value_hash_from_redis($group, 'users');

                if (intval($users) < 2) {
                    continue;
                }
            }

            $result[] = [
                'group_id'   => get_value_hash_from_redis($group, 'group_id'),
                'group_name' => get_value_hash_from_redis($group, 'group_name'),
                'users'      => get_value_hash_from_redis($group, 'users'),
                'created_at' => get_value_hash_from_redis($group, 'created_at'),
                'updated_at' => get_value_hash_from_redis($group, 'updated_at'),
            ];
        }

        return ResponseUtil::success('获取群组列表成功', $result);
    }

    /**
     * 创建群组
     *
     * @param $args
     * @return array
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function create($args)
    {
        // 检查参数
        $this->validateArguments($args, 'group_name');
        $this->validateArguments($args, 'users');

        $groupName = $args['group_name'];
        $users     = $args['users'];

        // 检查群组是否存在
        $groupId = get_group_id($groupName);

        if (exists_key_redis(RedisKeys::GROUP_PREFIX.$groupId)) {
            throw new BadRequestException('Group has already exists', ResponseUtil::HTTP_ERROR);
        }

        if (! is_array($users)) {
            throw new BadRequestException('Argument is not valid: users is not array', ResponseUtil::HTTP_BAD_REQUEST);
        }

        set_value_hash_to_redis(RedisKeys::GROUP_PREFIX.$groupId, 'group_id', $groupId);
        set_value_hash_to_redis(RedisKeys::GROUP_PREFIX.$groupId, 'group_name', $groupName);
        set_value_hash_to_redis(RedisKeys::GROUP_PREFIX.$groupId, 'users', count($users));
        set_value_hash_to_redis(RedisKeys::GROUP_PREFIX.$groupId, 'created_at', time());
        set_value_hash_to_redis(RedisKeys::GROUP_PREFIX.$groupId, 'updated_at', time());

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
     * @return array
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function append($args)
    {
        // 检查参数
        $this->validateArguments($args, 'group_id');
        $this->validateArguments($args, 'users');

        // 检查群组是否存在
        $groupId = $args['group_id'];

        if (! exists_key_redis(RedisKeys::GROUP_PREFIX.$groupId)) {
            throw new BadRequestException('Group is not exists', ResponseUtil::HTTP_ERROR);
        }

        $users = $args['users'];

        // 增加群人数
        increase_value_hash_in_redis(RedisKeys::GROUP_PREFIX.$groupId, 'users', count($users));

        // 增加群成员列表
        $members = get_all_list_element_from_redis(RedisKeys::GROUP_USER_LIST_PREFIX.$groupId);

        if (empty($members)) {
            $members = [];
        }

        $intersect = [];

        foreach ($users as $user) {
            if (! in_array($user, $members)) {
                $intersect[] = $user;
            }
        }

        push_array_to_list_in_redis(RedisKeys::GROUP_USER_LIST_PREFIX.$groupId, $intersect);

        return ResponseUtil::success('加入群组成功', $intersect);
    }

    /**
     * 获取指定群组的用户列表
     *
     * @param $args
     * @return array
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function users($args)
    {
        // 检查参数
        $this->validateArguments($args, 'group_id');

        // 检查群组是否存在
        $groupId = $args['group_id'];

        if (! exists_key_redis(RedisKeys::GROUP_PREFIX.$groupId)) {
            throw new BadRequestException('Group is not exists', ResponseUtil::HTTP_ERROR);
        }

        $members = get_all_list_element_from_redis(RedisKeys::GROUP_USER_LIST_PREFIX.$groupId);

        // 去除重复
        $members = array_unique($members);

        return ResponseUtil::success('获取群组用户列表成功', array_values($members));
    }
}