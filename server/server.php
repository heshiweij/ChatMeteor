<?php
/**
 * @author      svenhe <heshiweij@gmail.com>
 * @copyright   Copyright (c) Sven.He
 *
 * @link        http://www.svenhe.com
 */

use App\Config\ConfigAdapter;
use App\Dispatcher;
use App\Exceptions\JSONParseException;
use App\Lib\Redis\RedisClient;
use App\Lib\Redis\RedisKeys;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;
use App\Lib\Table\SwooleTableClient;

// get 'host' by cli args
if (isset($argv[1]) && ! empty($argv[1])) {
    define('HOST', $argv[1]);
}

// get 'port' by cli args
if (isset($argv[2]) && ! empty($argv[2])) {
    define('PORT', $argv[2]);
}

// get 'cpu core num' by cli args
if (isset($argv[3]) && ! empty($argv[3])) {
    define('CPU_CORES', $argv[3]);
}

/**
 * Web Socket & Http Server
 */
class WebSocket
{
    /**
     * which host listen at
     *
     * @var string
     */
    const HOST = '0.0.0.0';

    /**
     * which port listen at
     *
     * @var integer
     */
    const PORT = 8000;

    /**
     * worker number
     *
     * @var integer
     */
    const WORKER_NUM = 2;

    /**
     * System runtime log path
     *
     * @var string
     */
    const SYS_LOG_DIRECTORY = '/var/log/chat-meteor/system';

    /**
     * WebSocket instance
     *
     * @var \Swoole\WebSocket\Server
     */
    private $ws;

    public function __construct()
    {
        $this->ws = new Swoole\Websocket\Server(//
            defined('HOST') ? HOST : self::HOST, // default: 0.0.0.0
            defined('PORT') ? PORT : self::PORT); // default: 8000

        $this->initializeSettings();

        $this->ws->on('start', [$this, 'onStart']);
        $this->ws->on('workerStart', [$this, 'onWorkerStart']);

        $this->ws->on('open', [$this, 'onOpen']);
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on('close', [$this, 'onClose']);

        $this->ws->on('request', [$this, 'onRequest']);

        $this->ws->on('task', [$this, 'onTask']);
        $this->ws->on('finish', [$this, 'onFinish']);

        $this->ws->start();
    }

    /**
     * initialize for settings
     */
    private function initializeSettings()
    {
        $this->ws->set([
            //'reactor_num'     => 2,
            'worker_num'      => defined('CPU_CORES') ? CPU_CORES * 2 : self::WORKER_NUM,
            'task_worker_num' => defined('CPU_CORES') ? CPU_CORES * 4 : self::WORKER_NUM * 2,
            'daemonize'       => true,
            'log_file'        => self::SYS_LOG_DIRECTORY.'/'.date('Y-m-d').'.log',
            //'max_request'     => 1000,
            //'max_conn'        => 1000,
        ]);
    }

    /**
     * master process start(in master process)
     *
     * @param \Swoole\Server $server
     */
    function onStart(Swoole\Server $server)
    {
        swoole_set_process_name('chat-meteor');
    }

    /**
     * worker process start (in worker process)
     *
     * @param \Swoole\WebSocket\Server $server
     * @param $request
     */
    public function onWorkerStart(Swoole\Websocket\Server $server, $workerId)
    {
        require __DIR__.'/../public/index.php';

        // load config for worker process
        $this->loadConfiguration();

        // remove all online when restart
        if ($workerId == 0) {
            // can not run async client in task worker
            // so worker = 0 must be worker ,not task worker
            $redis = RedisClient::instance();

            if ($redis != null) {
                $redis->doSomething('del', [RedisKeys::USER_ONLINE_LIST]);
                $redis->doSomething('del', [RedisKeys::USER_ONLINE_LIST_REVERSE]);
            }
        }

        if (ConfigAdapter::isDebug()) {
            if (! $server->taskworker) {
                echo 'Worker Starting...'.$workerId.PHP_EOL;
            } else {
                echo 'Task Worker Starting...'.$workerId.PHP_EOL;
            }
        }
    }

    /**
     * load
     */
    private function loadConfiguration()
    {
        //load configuration
        try {
            $config = Yaml::parseFile(APP_PATH.'/Config/config.yaml');

            if (! is_array($config)) {
                throw new ParseException("Config must be array", 0);
            }

            $swooleTable = SwooleTableClient::instance();

            foreach ($config as $key => $entry) {
                $swooleTable->set($key, json_encode($entry));
            }
        } catch (Exception $e) {
            // log in file
        }
        //global $config;
    }

    /**
     * (in worker process)
     *
     * @param \Swoole\WebSocket\Server $server
     * @param $request
     */
    public function onOpen(Swoole\WebSocket\Server $server, $request)
    {
        echo "server: handshake success with fd{$request->fd}\n";
    }

    /**
     * (in worker process)
     *
     * @param \Swoole\WebSocket\Server $server
     * @param $frame
     */
    public function onMessage(Swoole\WebSocket\Server $server, $frame)
    {
        // parse message

        $_SERVER['server']  = $this->ws;
        $_SERVER['frame']   = $frame;
        $_SERVER['message'] = $frame->data;

        require __DIR__.'/../app/Core/dispatcher.php';

        /*
        if (! empty($message)) {

            try {
                $assoc = json_decode($message, true);

                throw new JSONParseException(json_last_error_msg(), json_last_error()." ==>  $message");

            } catch (Exception $e) {
                // todo: log it
            }
        }
        */

        /*
        foreach ($server->connections as $fd) {
            $server->push($fd, $message);
        }
        */
        //$server->push($frame->fd, $message);
    }

    /**
     * handle http protocol request (in worker process)
     *
     * @param \Swoole\Http\Request $request
     * @param \Swoole\Http\Response $response
     */
    public function onRequest(Swoole\Http\Request $request, Swoole\Http\Response $response)
    {
        //global $config;
        // clear all global variables
        //$_GET = $_POST = $_COOKIE = $_FILES = $_SERVER = $_REQUEST = [];

        // reset all global variables
        $_GET = $request->get;

        $_POST = $request->post;

        $_COOKIE = $request->cookie;

        $_FILES = $request->files;

        $_SERVER = $request->server;

        $_REQUEST = $request->request;

        // clear all global variables

        //var_dump($request);
        //var_dump($request->rawcontent());

        $_SERVER['server']   = $this->ws;
        $_SERVER['request']  = $request;
        $_SERVER['response'] = $response;

        ob_start();
        require __DIR__.'/../app/Core/core.php';

        $responseContent = ob_get_contents();

        $response->header('Content-Type', 'application/json');

        $response->end($responseContent);
        //ob_clean();
    }

    /**
     * handle task (in task worker process)
     *
     * @param \Swoole\Websocket\Server $server
     * @param $taskId
     * @param $srcWorkerId
     * @param $data
     */
    public function onTask(Swoole\Websocket\Server $server, $taskId, $srcWorkerId, $data)
    {
        return true;
    }

    /**
     * finish handle task (in worker process)
     *
     * @param \Swoole\Websocket\Server $server
     * @param $taskId
     * @param $result
     */
    public function onFinish(Swoole\Websocket\Server $server, $taskId, $result)
    {
        return true;
    }

    /**
     * (in worker process)
     *
     * @param \Swoole\Websocket\Server $server
     * @param $fd
     */
    public function onClose(Swoole\Websocket\Server $server, $fd)
    {
        // delete fd from redis

        // 01 take user id by fd
        $userId = RedisClient::instance()->doSomething('hget', [RedisKeys::USER_ONLINE_LIST, $fd]);

        // 02 delete USER_ONLINE_LIST row
        RedisClient::instance()->doSomething('hdel', [RedisKeys::USER_ONLINE_LIST, $fd]);

        // 03 delete USER_ONLINE_LIST_REVERSE row
        RedisClient::instance()->doSomething('hdel', [RedisKeys::USER_ONLINE_LIST_REVERSE, $userId]);
    }
}

$ws = new WebSocket();