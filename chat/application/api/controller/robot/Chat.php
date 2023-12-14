<?php

namespace app\api\controller\robot;

use app\common\model\robot\Vip;
use app\common\model\robot\Channelmodel;
use Workerman\Timer;
use Workerman\Connection\TcpConnection;
use app\api\library\robot\WeChat;
use app\common\model\robot\Assistant;
use app\common\model\robot\Creation;
use app\common\model\robot\Channel;
use app\common\model\robot\Session;
use app\common\model\robot\User;

use think\Db;

class Chat extends Base
{

    /**
     * 创建对话
     */
    public function create()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $param = $this->request->post();

        $platform = isset($param['platform']) ? $param['platform'] : 'wx';
        $assistantId = isset($param['assistant_id']) ? $param['assistant_id'] : 0;
        if (!$assistantId) {
            $this->response(2001);
        }

        $assistantModel = new Assistant();
        $assistant = $assistantModel->getAssistantInfoByChannel($this->channel['id'], $assistantId);
        if (!$assistant) {
            $this->response(2003);
        }

        $input = isset($param['input']) ? $param['input'] : '';
        $input = json_decode($input, true);
        if (!$input) {
            $this->response(2002);
        }

        foreach ($input as $k => $v) {
            if (!$v['tag']) {
                $this->response(2004);
            }
            if ($v['require'] && !$v['val']) {
                $this->response(9999, [], '请输入' . $v['title']);
            }
            $v['val'] = trim($v['val']);
            if (($v['type'] == 'slider')) {
                $v['val'] = (int)$v['val'];
                if ($v['val'] > 1000) {
                    $v['val'] = 1000;
                }
            }
            $replace['[[' . $v['tag'] . ']]'] = $v['val'];
        }

        $prompt = str_replace(array_keys($replace), array_values($replace), $assistant['template']);

        if (!$this->_checkPrompt($prompt)) {
            $this->response(2005);
        }

        $stream = isset($param['stream']) ? $param['stream'] : 0;
        if (!$stream) {
            $stream = $this->channel['stream'] ?: 1;
        }

        $sessionModel = new Session();
        $session = $sessionModel->getActiveSessionByAssistant($this->uid, $assistant['id']);
        if (!$session) {
            $session = $sessionModel->createSession($this->uid, $assistant['id']);
        }

        $limit = $this->_limit();
        if (!$limit) {
            $this->response(2009);
        }

        if ($stream == 1) {
            try {
                $messageData = $this->_create($prompt);
                if (($messageData['code'] != 0) || (!$messageData['data'])) {
                    $this->response(2008);
                }

                $chatId = $this->_insertCreation($prompt, $param['input'], $messageData, $session['id'], $assistant['id'], $stream, $platform);

                if (!$chatId) {
                    $this->response(2008);
                }

                $response['chat_id'] = $chatId;
                $this->_setTodayNums('day_limit');
                $this->_setLimit();

            } catch (\Exception $e) {
                $this->response(2006);
            }

        }

        if ($stream == 2) {
            $messageData = [];
            $chatId = $this->_insertCreation($prompt, $param['input'], $messageData, $session['id'], $assistant['id'], $stream, $platform);
            if (!$chatId) {
                $this->response(2008);
            }
            $response['chat_id'] = $chatId;
        }

        $response['stream'] = $stream;
        $this->response(0, $response);

    }

    /**
     * 查看对话（AI回复）
     */
    public function getChat()
    {
        if (!$this->uid) {
            $this->response(1003);
        }

        $param = $this->request->post();

        $chatId = isset($param['chatId']) ? $param['chatId'] : 0;
        if (!$chatId) {
            $this->response(2007);
        }

        $creationModel = new Creation();
        $creation = $creationModel->getInfo($chatId, $this->uid);
        if (!$creation) {
            $this->response(2008);
        }
        $assistantModel = new Assistant();
        $assistant = $assistantModel->getAssistantInfoByChannel($this->channel['id'], $creation['assistant_id']);
        $data['msg'] = $creation['msg'];
        $data['assistant'] = $assistant['name'];
        $this->response(0, $data);
    }

    /**
     * 流式输出
     */
    public function getChatStream($worker)
    {

        $worker->onMessage = function (TcpConnection $connection, $msg) {
            $connection->lastMessageTime = time();
            parse_str(urldecode($msg), $arr);
            $token = $arr['token'];
            $chatId = $arr['chatId'];
            $uid = $this->_checkToken($token);
            if (!$uid) {
                $data = $this->_getResponseData(1003);
                $connection->send($data);
                exit;
            }

            if (!$chatId) {
                $data = $this->_getResponseData(2007);
                $connection->send($data);
                exit;
            }

            $creationModel = new Creation();
            $creation = $creationModel->getInfo($chatId, $uid);
            if (!$creation) {
                $data = $this->_getResponseData(2008);
                $connection->send($data);
                exit;
            }

            $channelModel = new Channel();
            $this->channel = $channelModel->getInfo($creation['channel_id']);
            $this->uid = $uid;

            if (!$this->channel) {
                $data = $this->_getResponseData(-2);
                $connection->send($data);
                exit;
            }

            $this->_setTodayNums('day_limit');
            $this->_setLimit();

            $res = $this->_create($creation['content'], 2, $connection);

            if (($res['code'] != 0) || (!$res['data'])) {
                $data = $this->_getResponseData(2008);
                $connection->send($data);
                exit;
            }

            $update['time'] = isset($res['time']) ? $res['time'] : 0;
            $update['model'] = isset($res['model']) ? $res['model'] : 0;
            $update['model_id'] = isset($res['model_id']) ? $res['model_id'] : 0;
            $update['tokens'] = isset($res['tokens']) ? $res['tokens'] : 0;
            $update['msg'] = isset($res['data']) ? $res['data'] : '';
            $update['updatetime'] = time();
            $creationModel->saveCreation($chatId, $uid, $update);

            $connection->close();
        };


        $worker->onWorkerStart = function ($worker) {
            Timer::add(10, function () use ($worker) {
                $time_now = time();
                foreach ($worker->connections as $connection) {
                    // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                    if (empty($connection->lastMessageTime)) {
                        $connection->lastMessageTime = $time_now;
                        continue;
                    }
                    // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                    if ($time_now - $connection->lastMessageTime > 15) {
                        $connection->close();
                    }
                }
            });

        };


        $worker->onClose = function (TcpConnection $connection) {
            echo "connection closed\n";
        };


        $worker->onConnect = function (TcpConnection $connection) {
            $connection->id = rand(100000, 999999);
            echo "new connection " . "\n";
        };
    }

    /**
     * pc端会话列表
     */
    public function getSession()
    {
        if (!$this->uid) {
            $this->response(0, []);
        }
        $model = new Session();
        $session = $model->getListByUser($this->uid);
        $this->response(0, $session);
    }

    /**
     * pc端修改会话名称
     */
    public function saveSession()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $id = $this->request->post('id');
        $title = $this->request->post('title');
        $title = mb_substr(trim($title), 0, 10);
        if (!$id || !$title) {
            $this->response(2004);
        }

        $data['desc'] = $title;
        $data['updatetime'] = time();
        $model = new Session();
        $res = $model->saveSession($this->uid, $id, $data);
        if (!$res) {
            $this->response(2003);
        }

        $session['title'] = $title;
        $this->response(0, $session);
    }

    /**
     * pc端删除会话
     */
    public function deleteSession()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $id = $this->request->post('id', 0);

        $data['status'] = 0;
        $data['updatetime'] = time();
        $model = new Session();
        $res = $model->saveSession($this->uid, $id, $data);
        if (!$res) {
            $this->response(2003);
        }
        $this->response(0);
    }


    /**
     * pc端对话列表
     */
    public function getSessionInfo()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $sessionId = $this->request->get('id');
        if (!$sessionId) {
            $this->response(2001);
        }
        $model = new Creation();
        $data = $model->getList($this->uid, $sessionId);
        foreach ($data as $k => $v) {
            $question = '';
            $jsonData = json_decode($v['question'], true);
            if (!$jsonData) {
                $data[$k]['question'] = $question;
                continue;
            }
            foreach ($jsonData as $key => $value) {
                $question .= $value['title'] . ': ' . $value['val'] . "\n";
            }
            $data[$k]['question'] = $question;
        }
        $this->response(0, $data);
    }

    /**
     * 小程序生成记录
     */
    public function getCreation()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $model = new Creation();
        $data = $model->getList($this->uid);
        foreach ($data as $k => $v) {
            $assistantName = $v['assistant_name'];
            $question = '';
            $jsonData = json_decode($v['question'], true);
            if (!$jsonData) {
                $data[$k]['question'] = $assistantName;
                continue;
            }
            foreach ($jsonData as $key => $value) {
                $question .= $value['title'] . ': ' . $value['val'] . "\n";
            }
            $data[$k]['question'] = $question;
            $data[$k]['anwser'] = mb_substr($v['anwser'], 0, 50) . "...";
            $data[$k]['assistant'] = $assistantName;
        }
        $this->response(0, $data);
    }

    /**
     * pc端创建会话
     */
    public function createSession()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $id = $this->request->post('assistant_id');
        if (!$id) {
            $this->response(2001);
        }
        $data = [];
        $sessionModel = new Session();
        $res = $sessionModel->createSession($this->uid, $id);
        if ($res['id']) {
            $model = new Assistant();
            $assistant = $model->getAssistantInfoByChannel($this->channel['id'], $id);
            $data['id'] = $res['id'];
            $data['assistant'] = $assistant['name'] ?: "";
        }
        $this->response(0, $data);
    }

    /**
     * 对话评分
     */
    public function score()
    {
        if (!$this->uid) {
            $this->response(1003);
        }

        $id = $this->request->post('id');
        $score = $this->request->post('score');
        if (!$id || !$score) {
            $this->response(2001);
        }

        $data['rate'] = $score;
        $model = new Creation();
        $model->saveCreation($id, $this->uid, $data);

        $this->response(0);
    }


    /**
     * 检查prompt合法性
     */
    private function _checkPrompt($prompt)
    {
        $model = new User();
        $userInfo = $model->getUserInfo($this->uid);
        $openid = $userInfo['openid'];
        $wechat = new WeChat();

        if (!$openid) {
            return true;
        }

        $res = $wechat->msgSecCheck($prompt, $openid, $this->channel);
        if ($res != 100) {
            return false;
        }
        return true;
    }

    /**
     * 判断是否有生成次数
     */
    private function _limit()
    {
        $model = new Vip();
        $vip = $model->getInfo($this->uid);
        if (isset($vip['num']) && $vip['num'] > 0) {
            return true;
        }
        $dayVipNum = $this->_getTodayNums('today_vip_num');
        $dayNum = $this->_getTodayNums('day_limit');
        $num = $this->channel['free_num'];
        $shareNum = $this->_getTodayNums('share_limit');
        $adNum = $this->_getTodayNums('ad_limit');
        if (($dayNum - $dayVipNum) >= ($num + $shareNum + $adNum)) {
            return false;
        }
        return true;
    }

    /**
     * 使用生成次数
     */
    private function _setLimit()
    {
        $dayVipNum = $this->_getTodayNums('today_vip_num');
        $dayNum = $this->_getTodayNums('day_limit');
        $num = $this->channel['free_num'];
        $shareNum = $this->_getTodayNums('share_limit');
        $adNum = $this->_getTodayNums('ad_limit');

        if (($dayNum - $dayVipNum) > ($num + $shareNum + $adNum)) {
            $this->_setTodayNums('today_vip_num');
            $model = new Vip();
            $model->useNum($this->uid);
        }
        return true;
    }


    /**
     * 按序使用模型生成，如生成失败更换下一个
     */
    private function _create($prompt, $stream = 1, $connection = '')
    {

        $model = new Channelmodel();
        $models = $model->getModelListByChannel($this->channel['id']);
        $models = array_slice($models, 0, 4);

        $res = [];

        foreach ($models as $k => $model) {
            $time = time();
            $param['model'] = $model['model_tag'];
            $param['temperature'] = $model['temperature'];
            $content = [['role' => 'user', 'content' => $prompt]];
            $param['contents'] = $content;
            $channel = $this->_getChannelClass($model['model_class']);
            if ($stream == 2) {
                $res = $channel->chatStream($param, $model, $connection);
            } else {
                $res = $channel->chat($param, $model);
            }


            if ($res['code'] == 0) {
                $res['time'] = time() - $time;
                $res['model_id'] = $model['id'];
                break;
            }

            try {
                $error['channel'] = $this->channel['name'];
                $error['model'] = $model['model_tag'];
                $error['user_id'] = $this->uid;
                $error['prompt'] = $prompt;
                $error['code'] = $res['code'];
                $error['msg'] = $res['msg'];
                $error['stream'] = $stream;
                $error['created_date'] = date('Y-m-d H:i:s');
                Db::name('robot_error_log')->insert($error);
            } catch (\Exception $e) {

            }
        }
        return $res;
    }

    /**
     * 新增对话
     */
    private function _insertCreation($prompt, $input, $messageData, $sessionId, $assistantId, $stream, $platform)
    {
        $data['user_id'] = $this->uid;
        $data['session_id'] = $sessionId;
        $data['channel_id'] = $this->channel['id'];
        $data['model_id'] = isset($messageData['model_id']) ? $messageData['model_id'] : 0;
        $data['assistant_id'] = $assistantId;
        $data['content'] = $prompt;
        $data['input'] = $input;
        $data['stream'] = $stream;
        $data['msg'] = isset($messageData['data']) ? $messageData['data'] : '';
        $data['model'] = isset($messageData['model']) ? $messageData['model'] : '';
        $data['tokens'] = isset($messageData['tokens']) ? $messageData['tokens'] : 0;
        $data['ip'] = request()->ip();
        $data['platform'] = $platform;
        $data['time'] = isset($messageData['time']) ? $messageData['time'] : 0;
        $data['updatetime'] = $data['createtime'] = time();
        Creation::insert($data);
        $data['id'] = Creation::getLastInsID();
        return $data['id'];
    }

}
