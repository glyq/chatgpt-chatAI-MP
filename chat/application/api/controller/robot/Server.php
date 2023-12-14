<?php

namespace app\api\controller\robot;

use Workerman\Worker;

class Server
{
    public function chat()
    {
        Vendor('workerman.Autoloader');

        $worker = new Worker('websocket://0.0.0.0:8000');
        $worker->count = 8;
        $chat = new Chat();
        $chat->getChatStream($worker);
        Worker::runAll();
    }

    public function login()
    {
        Vendor('workerman.Autoloader');

        $worker = new Worker('websocket://0.0.0.0:7000');
        $worker->count = 8;
        $user = new User();
        $user->loginServer($worker);
        Worker::runAll();
    }
}
