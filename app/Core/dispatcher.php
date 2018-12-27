<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

use App\Lib\Log\LogClient;
use App\Utils\ResponseUtil;
use App\Exceptions\BadRequestException;

// take RawContent
$rawContent = $_SERVER['frame']->data;

try {

    // parse
    $assoc = json_decode($rawContent, true);

    if (! isset($assoc['type']) || empty($assoc['type'])) {
        throw new BadRequestException('The type can not be null', ResponseUtil::HTTP_BAD_REQUEST);
    }

    if (! isset($assoc['class']) || empty($assoc['class'])) {
        throw new BadRequestException('The class can not be null', ResponseUtil::HTTP_BAD_REQUEST);
    }

    if (! isset($assoc['method']) || empty($assoc['method'])) {
        throw new BadRequestException('The method can not be null', ResponseUtil::HTTP_BAD_REQUEST);
    }

    $type   = $assoc['type'];
    $class  = $assoc['class'];
    $method = $assoc['method'];
    $args   = $assoc['args'] ?: [];

    switch ($type) {

        // http request will access Http folder in \App\Api
        case 'ws':

            $class  = ucwords($class);
            $clazz  = "\\App\\Api\\Ws\\${class}";
            $obj    = new $clazz;
            $result = call_user_func_array([$obj, $method], [
                'data' => $args,
            ]);

            return;
    }
} catch (Exception $e) {

    LogClient::instance()->write('Ws-Dispatcher', [
        'code'    => $e->getCode(),
        'message' => $e->getMessage(),
    ]);
}
