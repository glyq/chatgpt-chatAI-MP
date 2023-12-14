<?php

namespace app\api\controller\robot;

use app\common\controller\Api;
use app\common\model\robot\Creation;
use think\Cache;
use app\common\model\robot\Channel;
use app\common\model\robot\Usertoken;
use app\common\model\robot\Vip;
use PHPMailer\PHPMailer\PHPMailer;


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
        $dayNum = Cache::tag('robot')->get($cacheKey);
        return $dayNum ?: 0;
    }

    protected function _setTodayNums($key, $addNum = 1)
    {
        $dayNum = $this->_getTodayNums($key);
        $day = date('Ymd');
        $cacheKey = $key . $day . $this->uid;
        return Cache::tag('robot')->set($cacheKey, $dayNum + $addNum, 86400);
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
        ];
        if (!$msg) {
            $msg = $message[$code];
        }
        $data['code'] = $code;
        $data['message'] = $msg;
        $data['data'] = $responseData;
        return json_encode($data);

    }


}