<?php

namespace app\common\model\robot;

use think\Model;


class Buy extends Model
{


    // 表名
    protected $name = 'robot_buy';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'type_text',
        'status_text',
        'channel_id_text'
    ];


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function getListByChannel($channelId, $type = 0)
    {
        $map['channel_id'] = $channelId;
        $map['status'] = 1;
        $type && $map['type'] = $type;
        $list = $this->where($map)->order('weigh desc')->select();
        return $list;
    }

    public function getInfoByChannel($channelId, $type = 0, $id = 0)
    {
        $map['channel_id'] = $channelId;
        $map['status'] = 1;
        $type && $map['type'] = $type;
        $id && $map['id'] = $id;
        $info = $this->where($map)->order('weigh desc')->find();
        return $info;
    }

    public function getTypeList()
    {
        return ['1' => __('Type 1'), '2' => __('Type 2'), '3' => __('Type 3'), '4' => __('Type 4'),'5' => __('Type 5'),'6' => __('Type 6'),'7' => __('Type 7')];
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '0' => __('Status 0')];
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


    public function getTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['type']) ? $data['type'] : '');
        $list = $this->getTypeList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
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
