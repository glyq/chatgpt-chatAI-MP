<?php

namespace app\common\model\robot;

use think\Model;


class Channelassistant extends Model
{


    // 表名
    protected $name = 'robot_channel_assistant';

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


    protected static function init()
    {

        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function getRelationAssistant($channelId = 0)
    {
        $map['b.status'] = 1;
        $map['a.channel_id'] = $channelId;
        $list = self::where($map)
            ->alias('a')
            ->field('b.id assistant_id,b.name,b.cate_id')
            ->join('robot_assistant b', 'a.assistant_id = b.id')
            ->order('a.weigh desc')
            ->select();
        $list = collection($list)->toArray();
        return $list;
    }

    public function getChannelIdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['channel_id']) ? $data['channel_id'] : '');
        $list = $this->getCateIdList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getCateIdList($channelId = 0)
    {
        $cate = new Channelcate();
        $list = $cate->getCateListByChannel($channelId);
        $cateList = [];
        foreach ($list as $k => $v) {
            $cateList[$v['id']] = $v['name'];
        }
        return $cateList;
    }

    public function assistant()
    {
        return $this->belongsTo('Assistant', 'assistant_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
