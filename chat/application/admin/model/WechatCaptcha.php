<?php

namespace app\admin\model;

use fast\Random;
use think\Model;

class WechatCaptcha extends Model
{

    // 表名
    protected $name = 'wechat_captcha';
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';
    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = '';
    // 追加属性
    protected $append = [
    ];

    /**
     * 发送验证码
     * @param $openid  string 用户OpenID
     * @param $event   string 事件
     * @param $ip      string IP地址
     * @return string
     */
    public static function send($openid, $event, $ip)
    {
        $captcha = self::where(['openid' => $openid, 'event' => $event])->whereTime('createtime', '-2 minutes')->find();
        if ($captcha) {
            return "验证码发送速度过快，请稍后重试";
        }
        $code = Random::alnum(4);
        $data = [
            'event'  => $event,
            'openid' => $openid,
            'code'   => $code,
            'ip'     => $ip,
        ];
        self::create($data);
        return "你的验证码是：{$code}，2分钟内输入有效";
    }

    /**
     * 检测验证码
     * @param $code  string 验证码
     * @param $event string 事件
     * @param $ip    string IP
     * @return bool
     */
    public static function check($code, $event, $ip = null)
    {
        $ip = is_null($ip) ? request()->ip() : $ip;
        $captcha = self::where(['ip' => $ip, 'event' => $event])->whereTime('createtime', '-2 minutes')->find();
        if ($captcha && $captcha->code == $code && $captcha->times < 10) {
            $captcha->setInc("times");
            return true;
        }
        //验证大于10次或超时
        if ($captcha && ($captcha->times >= 10 || time() - $captcha->createtime > 120)) {
            $captcha->delete();
        }

        return false;
    }

}
