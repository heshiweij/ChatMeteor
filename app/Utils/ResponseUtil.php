<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Utils;

class ResponseUtil
{
    // 响应成功
    const HTTP_SUCCESS = 200;

    // 响应失败，格式错误
    const HTTP_BAD_REQUEST = 400;

    // 响应失败，鉴权错误
    const HTTP_AUTH_ERROR = 401;

    // 响应失败，禁止访问
    const HTTP_FORBIDDEN_ERROR = 403;

    // 响应失败，没有找到
    const HTTP_NOT_FOUND_ERROR = 404;

    // 响应失败，验证错误
    const HTTP_VALIDATION_ERROR = 422;

    // 响应失败，其他错误
    const HTTP_ERROR = 500;

    /**
     * for response success
     *
     * @param $message
     * @param array $data
     * @return array
     */
    public static function success($message, $data = [])
    {
        return [
            'code'    => self::HTTP_SUCCESS,
            'message' => $message,
            'data'    => $data,
        ];
    }

    /**
     * for response failure
     *
     * @param $code
     * @param $message
     * @return array
     */
    public static function failure($code, $message)
    {
        return [
            'code'    => $code,
            'message' => $message,
        ];
    }
}