<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

use App\Exceptions\ParameterIllegalException;
use App\Lib\Redis\RedisClient;
use App\Lib\Redis\RedisKeys;
use App\Utils\ResponseUtil;

/** 从 redis 的 hash 结构中，根据 key，取得 value
 *
 * @param $hash
 * @param $key
 * @return
 * @throws \App\Exceptions\ParameterIllegalException
 */
function get_value_hash_from_redis($hash, $key)
{
    if (empty($hash) || empty($key)) {
        throw new ParameterIllegalException('Params: hash or key is empty', ResponseUtil::HTTP_ERROR);
    }

    $value = RedisClient::instance()->doSomething('hget', [
        $hash,
        $key,
    ]);

    if (empty($value)) {
        $value = '';
    }

    return $value;
}

/**
 * 在 redis 的 hash 结构中，设置键值对
 *
 * @param $hash
 * @param $key
 * @param $value
 * @return string
 * @throws \App\Exceptions\ParameterIllegalException
 */
function set_value_hash_to_redis($hash, $key, $value)
{
    if (empty($hash) || empty($key) || empty($value)) {
        throw new ParameterIllegalException('Params: hash, key or value is empty', ResponseUtil::HTTP_ERROR);
    }

    return RedisClient::instance()->doSomething('hset', [$hash, $key, $value]);
}

/**
 * 在 redis 的 hash 的键值对中，新增一个 key 的 value 为 value + step
 *
 * @param $hash
 * @param $key
 * @param int $step 此参数为负数，则为减少
 * @return bool
 */
function increase_value_hash_in_redis($hash, $key, $step = 1)
{
    return RedisClient::instance()->doSomething('hincrby', [$hash, $key, $step]);
}

/**
 * 在 redis 的 hash 的键值对中，减少一个 key 的 value 为 value + step
 *
 * @param $hash
 * @param $key
 * @param int $step 此参数为负数，则为增加
 * @return bool
 */
function decrease_value_hash_in_redis($hash, $key, $step = 1)
{
    return RedisClient::instance()->doSomething('hincrby', [$hash, $key, -$step]);
}

/**
 * 获取 redis 中 list 结构的所有元素
 *
 * @param $key
 * @return
 * @throws \App\Exceptions\ParameterIllegalException
 */
function get_all_list_element_from_redis($key)
{
    if (empty($key)) {
        throw new ParameterIllegalException('Params: key is empty', ResponseUtil::HTTP_ERROR);
    }

    return RedisClient::instance()->doSomething('lrange', [$key, 0, -1]);
}

/**
 * 判断 redis 的 list 结构中是否存在某个元素
 *
 * @param $key
 * @param $element
 * @return bool
 * @throws \App\Exceptions\ParameterIllegalException
 */
function exists_element_list_in_redis($key, $element)
{
    if (empty($key) || empty($element)) {
        throw new ParameterIllegalException('Params: key or element is empty', ResponseUtil::HTTP_ERROR);
    }

    $all = get_all_list_element_from_redis($key);

    return ! empty($all) && is_array($all) && in_array($element, $all);
}

/**
 * 往 redis 的 list 结构中推送 array 元素
 *
 * @param $key
 * @param array $elements
 * @return
 * @throws \App\Exceptions\ParameterIllegalException
 */
function push_array_to_list_in_redis($key, $elements = [])
{
    if (! is_array($elements)) {
        throw new ParameterIllegalException('Params: elements must be array', ResponseUtil::HTTP_ERROR);
    }

    $params = [$key];
    $params = array_merge($params, $elements);

    return RedisClient::instance()->doSomething('lpush', $params);
}

/**
 * 从 redis 中取出符合指定模式的 key 的集合
 *
 *  ?: 单个字符  *: 任意多个字符
 *
 * @param $pattern
 * @return mixed
 */
function get_keys_from_redis($pattern)
{
    $keys = RedisClient::instance()->doSomething('keys', [
        $pattern,
    ]);

    return $keys;
}

/**
 * redis 中的某个 key 是否存在
 *
 * @param $key
 * @return mixed
 */
function exists_key_redis($key)
{
    $exists = RedisClient::instance()->doSomething('exists', [$key]);

    return $exists;
}

/**
 * 根据 group_name，获得 group_id
 *
 * @param $groupName string 群组名
 * @return bool|string
 * @throws \App\Exceptions\ParameterIllegalException
 */
function get_group_id($groupName)
{
    if (empty($groupName) || empty($groupName)) {
        throw new ParameterIllegalException('Params: group name is empty', ResponseUtil::HTTP_ERROR);
    }

    return substr(md5($groupName), 0, 6);
}

