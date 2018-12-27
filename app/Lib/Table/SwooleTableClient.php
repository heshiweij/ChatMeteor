<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

namespace App\Lib\Table;

use Swoole\Table;

class SwooleTableClient
{
    /**
     * max row count for swoole memory table
     */
    const MAX_ROW = 16;

    /**
     * max bytes of string field
     */
    const MAX_SIZE_STRING = 256;

    /**
     * fixed column to store json string
     */
    const COLUMN_NAME = 'content';

    private static $instance;

    private $table;

    private function __construct()
    {
        // create swoole table instance
        $this->table = new Table(self::MAX_ROW);
        $this->table->column(self::COLUMN_NAME, Table::TYPE_STRING, self::MAX_SIZE_STRING);
        $this->table->create();
    }

    /**
     * forbid clone
     */
    public function __clone()
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
     * @param $key
     * @param string $value
     * @return mixed
     */
    public function set($key, $value = '')
    {
        return $this->table->set($key, [
            self::COLUMN_NAME => $value,
        ]);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->table->get($key, self::COLUMN_NAME);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function del($key)
    {
        return $this->table->del($key);
    }

    public function __destruct()
    {
        // release swoole table resource
        // the memory will be released automatic when swoole process finished
        $this->table = null;
    }
}