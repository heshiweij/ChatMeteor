<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Api\Http;

use App\Lib\Log\LogClient;
use App\Lib\MySql\MySqlClient;
use App\Lib\Redis\RedisClient;
use App\Lib\Redis\RedisKeys;
use App\Lib\Table\SwooleTableClient;
use App\Traits\Validation;
use App\Utils\ResponseUtil;

class Single
{
    use Validation;

    /**
     * 查看某个用户是否在线
     *
     * @param $args
     * @return array
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function online($args)
    {
        // 检查参数
        $this->validateArguments($args, 'user_id');

        $userId = $args['user_id'];

        $value = get_value_hash_from_redis(RedisKeys::USER_ONLINE_LIST_REVERSE, $userId);

        return ResponseUtil::success('获取用户在线状态成功', [
            'online' => ! empty($value),
        ]);
    }

    /**
     * 获取在线用户列表
     *
     * @param $args
     * @return array
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function onlineList($args)
    {
        $server = $_SERVER['server'];

        $connections = $server->connections;

        $users = [];

        foreach ($connections as $fd) {
            $userId = get_value_hash_from_redis(RedisKeys::USER_ONLINE_LIST, $fd);

            if (! empty($userId)) {
                $users[] = $userId;
            }
        }

        return ResponseUtil::success('获取在线用户列表成功', $users);
    }

    /**
     * 获得当前用户所在的群
     *
     * @param $args
     * @return array
     * @throws \App\Exceptions\BadRequestException
     * @throws \App\Exceptions\ParameterIllegalException
     */
    public function group($args)
    {
        // 检查参数
        $this->validateArguments($args, 'user_id');

        $userId = $args['user_id'];

        $groups = get_keys_from_redis(RedisKeys::GROUP_PREFIX.str_repeat('?', 6));

        $result = [];

        foreach ($groups as $group) {

            $array = explode('_', $group);

            if (count($array) === 2) {
                $groupId = $array[1];

                $members = get_all_list_element_from_redis(RedisKeys::GROUP_USER_LIST_PREFIX.$groupId);

                if (is_array($members) && in_array($userId, $members)) {
                    $result[] = $groupId;
                }
            }
        }

        return ResponseUtil::success('获取用户所在的群组成功', $result);
    }
}