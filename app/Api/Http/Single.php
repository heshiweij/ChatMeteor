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
use App\Lib\Table\SwooleTableClient;
use App\Utils\ResponseUtil;

class Single
{
    /**
     * @param $args
     * @return array
     * @throws \App\Exceptions\ResourceConnectException
     */
    public function index($args)
    {
        //$name = RedisClient::instance()->doSomething('mget', [
        //    ['name', 'haha'],
        //]);
        //
        //var_dump($name);

        //phpinfo();

        //$result = MySqlClient::instance()->query("select * from single");

        //var_dump($result);

        /*
        LogClient::instance()->write('single', [
            'haha',
            'haha2',
            'xixi' => [
                'ni' => [
                    '1',
                    [
                        '1'  => '555',
                        '12' => '555',
                        '13' => '555',
                    ],
                ],
            ],
        ]);
        */

        return ResponseUtil::success("成功了", [
            'name' => 'hsw',
            'args' => $args,
        ]);
    }
}