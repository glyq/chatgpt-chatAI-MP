<?php

namespace app\common\model\robot;

use think\Model;


class Channelcate extends Model
{


    // 表名
    protected $name = 'robot_channel_cate';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function getCateListByChannel($channelId)
    {
        $map['b.status'] = 1;
        $map['a.channel_id'] = $channelId;
        $list = self::where($map)
            ->alias('a')
            ->field('b.id,b.name')
            ->join('robot_cate b', 'a.cate_id = b.id')
            ->order('a.weigh desc')
            ->select();
        $list = collection($list)->toArray();
        return $list;
    }


    public function cate()
    {
        return $this->belongsTo('Cate', 'cate_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
