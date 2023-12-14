<?php

namespace app\common\model;

use think\Model;


class Channelmodel extends Model
{

    

    

    // 表名
    protected $name = 'robot_channel_model';
    
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

    







    public function model()
    {
        return $this->belongsTo('app\common\model\robot\Model', 'model_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
