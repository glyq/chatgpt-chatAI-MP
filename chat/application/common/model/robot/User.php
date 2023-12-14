<?php

namespace app\common\model\robot;

use think\Model;


class User extends Model
{


    // 表名
    protected $name = 'robot_user';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'channel_id_text',
        'status_text'
    ];


    public function getChannelIdList()
    {
        $map['status'] = 1;
        $field = ['id', 'name'];
        $channel = new Channel();
        $list = $channel->getList($map, $field);
        $statusList = [];
        foreach ($list as $k => $v) {
            $statusList[$v['id']] = $v['name'];
        }
        return $statusList;
    }

    public function getUserByOpenid($openid)
    {
        $map['openid'] = $openid;
        $map['status'] = 1;
        $userInfo = $this->where($map)->find();
        $userInfo && $userInfo = $userInfo->toArray();
        return $userInfo;
    }

    public function getUserInfo($userId)
    {
        $map['id'] = $userId;
        $map['status'] = 1;
        $userInfo = $this->where($map)->find();
        $userInfo && $userInfo = $userInfo->toArray();
        return $userInfo;
    }

    public function createUser($channelId, $platform, $openid, $unionid)
    {
        $time = time();
        try {
            $data['openid'] = $openid;
            $data['unionid'] = $unionid;
            $data['channel_id'] = $channelId;
            $data['createtime'] = $time;
            $data['updatetime'] = $time;
            $data['status'] = 1;
            $data['head_img'] = cdnurl('/assets/static/images/touxiang.jpg');
            $data['desc'] = '';
            $data['platform'] = $platform;
            $data['nickname'] = $platform == 'wx' ? '微信用户' : '手机用户';
            $userInfo = $data;
            self::insert($data);
            $userId = self::getLastInsID();
            $userInfo['id'] = $userId;
        } catch (\Exception $e) {
            $userInfo = [];
        }
        return $userInfo;
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getChannelIdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['channel_id']) ? $data['channel_id'] : '');
        $list = $this->getChannelIdList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function channel()
    {
        return $this->belongsTo('Channel', 'channel_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function vip()
    {
        return $this->hasOne('Vip', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
