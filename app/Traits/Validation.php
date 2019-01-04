<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Traits;

use App\Exceptions\BadRequestException;
use App\Utils\ResponseUtil;

trait Validation
{
    /**
     * @param $args
     * @param $key
     * @throws \App\Exceptions\BadRequestException
     */
    public function validateArguments($args, $key)
    {
        if (! isset($args[$key]) || empty($args[$key])) {
            throw new BadRequestException('Argument is not valid: missing '.$key, ResponseUtil::HTTP_BAD_REQUEST);
        }
    }
}