<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

use App\Utils\ResponseUtil;
use App\Exceptions\BadRequestException;

// take RawContent
$rawContent = $_SERVER['request']->rawcontent();

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
        case 'http':

            $class  = ucwords($class);
            $clazz  = "\\App\\Api\\Http\\${class}";
            $obj    = new $clazz;
            $result = call_user_func_array([$obj, $method], [
                'data' => $args,
            ]);

            echo json_encode($result);

            return;
    }
} catch (Exception $e) {
    $result = ResponseUtil::failure($e->getCode(), $e->getMessage());

    echo json_encode($result);
}
