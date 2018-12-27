<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Lib\Log;

class LogClient
{
    private static $instance;

    private function __construct()
    {
    }

    public static function instance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * @param $tag
     * @param array $array
     */
    public function write($tag, array $array = [])
    {

        $date = date('Y-m-d');

        $content = date('Y-m-d H:i:s').' '.$tag.' ===> '.json_encode($array).PHP_EOL.PHP_EOL;

        swoole_async_writefile(LOG_PATH."/chat_$date.log", $content, function () {
            // todo
        }, FILE_APPEND);
    }

    /**
     * forbid clone
     */
    public function __clone()
    {
    }

    public function __destruct()
    {

    }
}