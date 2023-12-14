<?php

namespace app\common\model\robot;

use think\Model;
use traits\model\SoftDelete;

class Cate extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'robot_cate';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'status_text'
    ];

    public function getList($map, $field)
    {
        $list = self::where($map)->field($field)->order('id asc')->select();
        return $list;
    }

    public function getCateListByChannel($channelId)
    {
        $map['a.status'] = 1;
        $map['b.channel_id'] = $channelId;
        $cates = self::name('robot_cate')
            ->alias('a')
            ->field('a.id,a.name')
            ->join('robot_channel_cate b', 'a.id = b.cate_id')
            ->order('b.weigh desc')
            ->where($map)
            ->select();
        return $cates;
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


}
