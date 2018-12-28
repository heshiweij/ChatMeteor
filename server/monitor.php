<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 *
 * process health monitor
 */

// include composer autoload
require __DIR__.'/../public/index.php';

// get 'server_port' by cli args
if (! isset($argv[1]) || empty($argv[1]) || intval($argv[1] <= 0)) {
    exit("Please give server port".PHP_EOL);
}

define('SERVER_PORT', $argv[1]);

class Monitor
{
    public function process()
    {
        $rowCount = shell_exec('netstat -anp|grep '.SERVER_PORT.'|grep LISTEN|wc -l');

        $rowCount = intval(trim($rowCount));

        if ($rowCount !== 1) {

            echo date('Y-m-d H-i:s')."  => Process is terminated".PHP_EOL;

            // TODO
            // send mail

            // send sms

            // and more ...

        }
    }

    public function redis()
    {
        // TODO
        // refer: http://www.cnblogs.com/yeahwell/p/5330012.html
    }
}

// loop every 10 minutes
swoole_timer_tick(10000 * 60, function () {

    (new Monitor())->process();
});

//(new Monitor())->process();