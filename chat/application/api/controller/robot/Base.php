<?php

namespace app\api\controller\robot;

use app\api\library\robot\WeChat;
use app\common\controller\Api;
use app\common\model\robot\Channelmodel;
use app\common\model\robot\Creation;
use app\common\model\robot\User;
use think\Cache;
use app\common\model\robot\Channel;
use app\common\model\robot\Usertoken;
use app\common\model\robot\Vip;
use PHPMailer\PHPMailer\PHPMailer;
use think\Db;


class Base extends Api
{
    protected static $_channelInstance = [];
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];
    protected $uid = 0;
    protected $accessToken = '';
    protected $channel = [];

    public function _initialize()
    {
        if (request()->controller() != 'Robot.server') {
            $this->_checkSign();
        }
        $this->uid = $this->_checkToken();
    }

    protected function _getChannelClass($shortname)
    {

        if (isset(self::$_channelInstance[$shortname])) {
            return self::$_channelInstance[$shortname];
        }
        $class = new \ReflectionClass('app\api\library\robot\\' . $shortname);
        $instance = $class->newInstanceArgs();
        self::$_channelInstance[$shortname] = $instance;
        return $instance;
    }


    private function _checkSign()
    {
        try {
            $channelId = isset($_SERVER['HTTP_CHANNEL']) ? $_SERVER['HTTP_CHANNEL'] : 0;
            $param = $this->request->param();
            $appkey = isset($param['app_key']) ? $param['app_key'] : '';
            if (!$channelId || !$appkey) {
                $this->response(-1);
            }

            $requestSign = isset($param['sign']) ? $param['sign'] : '';
            if (time() > substr($param['timestamp'], 0, -3) + 300) {
                $this->response(-4);
            }

            $model = new Channel();
            $channel = $model->getInfo($channelId, $appkey);

            $this->channel = $channel;
            if (!$channel) {
                $this->response(-2);
            }

            unset($param['sign']);
            ksort($param);
            $str = '';
            foreach ($param as $k => $v) {
                $str .= $k . $v;
            }
            $str = $channel['appsecret'] . $str . $channel['appsecret'];
            $sign = strtoupper(md5($str));
            if ($requestSign != $sign) {
                $this->response(-3);
            }
        } catch (\Exception $e) {
            $this->response(-5);
        }

    }

    protected function _checkToken($accessToken = '')
    {
        if (!$accessToken) {
            $header = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
            $arr = explode(' ', $header);
            $accessToken = isset($arr[1]) ? $arr[1] : '';
        }

        if (!$accessToken) {
            return 0;
        }
        $map['token'] = $accessToken;
        $tokenInfo = Usertoken::where($map)->find();
        $this->accessToken = $accessToken;
        return isset($tokenInfo['user_id']) ? $tokenInfo['user_id'] : 0;
    }

    protected function _getTodayNums($key)
    {
        $day = date('Ymd');
        $cacheKey = $key . $day . $this->uid;
        $dayNum = Cache::get($cacheKey);
        return $dayNum ?: 0;
    }

    protected function _setTodayNums($key, $addNum = 1)
    {
        $dayNum = $this->_getTodayNums($key);
        $day = date('Ymd');
        $cacheKey = $key . $day . $this->uid;
        return Cache::set($cacheKey, $dayNum + $addNum, 86400);
    }

    protected function _getUserNums()
    {
        if (!$this->uid) {
            return [];
        }

        $model = new Vip();
        $vipInfo = $model->getInfo($this->uid);

        $vipNum = isset($vipInfo['num']) ? $vipInfo['num'] : 0;
        $dayNum = $this->_getTodayNums('day_limit');
        $num = $this->channel['free_num'];
        $shareNum = $this->_getTodayNums('share_limit');
        $adNum = $this->_getTodayNums('ad_limit');
        $dayVipNum = $this->_getTodayNums('today_vip_num');

        $freeNums = ($dayNum - $dayVipNum) >= ($num + $shareNum + $adNum) ? 0 : $num + $shareNum + $adNum - ($dayNum - $dayVipNum);
        $nums = $freeNums + $vipNum;

        $data['nums'] = $nums > 100000 ? '无限次' : $nums;
        $data['free_nums'] = $freeNums;
        $data['day_nums'] = $dayNum;
        $data['used_nums'] = Creation::where(['user_id' => $this->uid])->count();
        $data['show_vip'] = $nums > 100000 ? 0 : 1;
        return $data;
    }

    protected function _addVip($uid, $num)
    {
        $model = new Vip();
        return $model->addVip($uid, $num);
    }

    protected function response($code = 0, $responseData = [], $msg = '')
    {
        $data = $this->_getResponseData($code, $responseData, $msg);
        echo $data;
        exit;
    }

    protected function _getResponseData($code = 0, $responseData = [], $msg = '')
    {
        $message = [
            0 => '请求成功',
            -1 => '鉴权失败',
            -2 => '渠道鉴权失败',
            -3 => '暂无权限',
            -4 => '请求超时',
            -5 => '服务器错误',
            -6 => '参数错误',
            1000 => 'code鉴权错误',
            1001 => '用户信息获取失败',
            1002 => '登录失败',
            1003 => '登录已过期,请重新登录',
            2001 => '参数错误',
            2002 => '参数错误',
            2003 => '暂时不可用',
            2004 => '参数错误',
            2005 => '生成失败，请检查是否输入党政、色情等敏感词汇',
            2006 => '生成失败，请检查是否输入党政、色情等敏感词汇',
            2007 => '参数错误',
            2008 => '生成失败，请检查是否输入党政、色情等敏感词汇',
            2009 => '次数不足',
            3001 => '助力已经超过3次啦',
            3002 => '参数错误',
            3003 => '已经助力过啦',
            3004 => '不能给自己助力哦',
            4001 => '支付参数错误',
            4002 => '支付参数错误',
            5001 => '观看次数已超限',
            6001 => '已经是新话题了，继续聊吧',
            6002 => '输入字数超限，最多输入500字',
        ];
        if (!$msg) {
            $msg = $message[$code];
        }
        $data['code'] = $code;
        $data['message'] = $msg;
        $data['data'] = $responseData;
        return json_encode($data);

    }

    /**
     * 检查prompt合法性
     */
    protected function _checkPrompt($prompt)
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
    protected function _limit()
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
     * 按序使用模型生成，如生成失败更换下一个
     */
    protected function _create($prompt,$sessionId, $stream = 1, $connection = '')
    {

        $model = new Channelmodel();
        $models = $model->getModelListByChannel($this->channel['id']);
        $models = array_slice($models, 0, 4);

        $res = [];
        $content = [];
        if($this->channel['multiple']){
            $map['user_id'] = $this->uid;
            $map['session_id'] = $sessionId;
            $map['assistant_id'] = 0;
            $history = Db::name('robot_creation')->where($map)->order('id asc')->limit(2)->select();
            foreach ($history as $k=>$v){
                if($v['content'] && $v['msg']){
                    $content[] = ['role'=>'user','content'=>$v['content']];
                    $content[] = ['role'=>'assistant','content'=>$v['msg']];
                }
            }
        }
        $content[] = ['role' => 'user', 'content' => $prompt];

        foreach ($models as $k => $model) {
            $time = time();
            $param['model'] = $model['model_tag'];
            $param['temperature'] = $model['temperature'];
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
    protected function _insertCreation($prompt, $input, $messageData, $sessionId, $assistantId, $stream, $platform)
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




    /**
     * 使用生成次数
     */
    protected function _setLimit()
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



}