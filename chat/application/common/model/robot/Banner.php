<?php

namespace app\common\model\robot;

use think\Model;
use traits\model\SoftDelete;

class Banner extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'robot_banner';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'channel_id_text',
        'jump_type_text',
        'status_text'
    ];


    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
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

    public function getList($map)
    {
        $banner = self::name('robot_banner')->field('id,name,img,jump_type,appid,path')->order('weigh desc')->where($map)->select();
        foreach ($banner as $k => $v) {
            $banner[$k]['img'] = cdnurl($v['img']);
        }
        return $banner;
    }

    public function getJumpTypeList()
    {
        return ['1' => __('Jump_type 1'), '0' => __('Jump_type 0')];
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


    public function getJumpTypeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['jump_type']) ? $data['jump_type'] : '');
        $list = $this->getJumpTypeList();
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
}
