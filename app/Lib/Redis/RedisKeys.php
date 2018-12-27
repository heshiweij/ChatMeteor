<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Lib\Redis;

class RedisKeys
{
    /**
     * 在线用户列表
     *  type：list
     *  数据：1
     *  作用：存放 user_id 和 fd
     *      fd =>  user_id
     */
    const USER_ONLINE_LIST = 'user_online_list';

    /**
     * 在线用户列表(键值反向)
     *  type：list
     *  数据：1
     *  作用：存放 user_id 列表
     *      user_id => fd
     */
    const USER_ONLINE_LIST_REVERSE = 'user_online_list_reverse';

    /**
     * 群组前缀
     *  type：hash
     *  数量：= 群的数量
     *  作用：存放每个群组的信息，如：group_e10adc 包含：
     *          group_id ( = substr(md5(group_name), 0, 6) ) 群组 id
     *          group_name 群组名
     *          updated_at 最后活跃时间
     *
     */
    const GROUP_PREFIX = "group_";

    /**
     * 群组用户列表前缀
     *  type：list
     *  数量：= 群的数量
     *  作用：存放每个群的用户列表(user_id)
     */
    const GROUP_USER_LIST_PREFIX = 'group_user_list_';
}