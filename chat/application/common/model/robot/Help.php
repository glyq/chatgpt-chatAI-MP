<?php

namespace app\common\model\robot;

use think\Model;


class Help extends Model
{


    // 表名
    protected $name = 'robot_help';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'channel_id_text'
    ];


    public function getList($pid = 0, $userId = 0, $limit = 20)
    {
        $pid && $map['a.pid'] = $pid;
        $userId && $map['a.user_id'] = $userId;
        $helpInfo = $this
            ->alias('a')
            ->field('b.id,a.pid,b.head_img')
            ->join('robot_user b', 'a.user_id = b.id')
            ->where($map)
            ->limit($limit)
            ->select();
        $helpInfo = collection($helpInfo)->toArray();
        foreach ($helpInfo as $k => $v) {
            $helpInfo[$k]['head_img'] = cdnurl($v['head_img']);
        }
        return $helpInfo;
    }

    public function createHelp($channelId, $uid, $pid)
    {
        $data['user_id'] = $uid;
        $data['pid'] = $pid;
        $data['channel_id'] = $channelId;
        $data['createtime'] = time();
        $res = self::insert($data);
        return $res;
    }

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


    public function getChannelIdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['channel_id']) ? $data['channel_id'] : '');
        $list = $this->getChannelIdList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function channel()
    {
        return $this->belongsTo('Channel', 'channel_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
