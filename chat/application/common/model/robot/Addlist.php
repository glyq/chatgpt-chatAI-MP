<?php

namespace app\common\model\robot;

use think\Model;


class Addlist extends Model
{


    // 表名
    protected $name = 'robot_addnums';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'channel_id_text',
        'key_text'
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

    public function addNums($key, $nums, $uid, $channelId)
    {
        $data['key'] = $key;
        $data['addnums'] = $nums;
        $data['user_id'] = $uid;
        $data['channel_id'] = $channelId;
        $data['createtime'] = time();
        return $this->insert($data);
    }

    public function getKeyList()
    {
        return ['share_limit' => __('Key share_limit'), 'ad_limit' => __('Key ad_limit')];
    }


    public function getChannelIdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['channel_id']) ? $data['channel_id'] : '');
        $list = $this->getChannelIdList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getKeyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['key']) ? $data['key'] : '');
        $list = $this->getKeyList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function channel()
    {
        return $this->belongsTo('Channel', 'channel_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
