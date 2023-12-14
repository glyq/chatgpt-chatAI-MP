<?php

namespace app\common\model\robot;

use think\Model;
use traits\model\SoftDelete;

class Channel extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'robot_channel';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'stream_text',
        'status_text',
        'show_stream_text',
        'stream_default_text',
        'show_ad_text',
        'show_vip_text'
    ];

    public function getList($map, $field)
    {
        $list = self::where($map)->field($field)->order('id desc')->select();
        return $list;
    }

    public function getInfo($id, $appkey = '')
    {
        $map['status'] = 1;
        $map['id'] = $id;
        $appkey && $map['appkey'] = $appkey;
        $data = $this->where($map)->find()->toArray();
        return $data;
    }


    public function getStreamList()
    {
        return ['2' => __('Stream 2'), '1' => __('Stream 1')];
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }

    public function getShowStreamList()
    {
        return ['1' => __('Show_stream 1'), '0' => __('Show_stream 0')];
    }

    public function getStreamDefaultList()
    {
        return ['1' => __('Stream_default 1'), '0' => __('Stream_default 0')];
    }

    public function getShowAdList()
    {
        return ['1' => __('Show_ad 1'), '0' => __('Show_ad 0')];
    }

    public function getShowVipList()
    {
        return ['1' => __('Show_vip 1'), '0' => __('Show_vip 0')];
    }


    public function getStreamTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['stream']) ? $data['stream'] : '');
        $list = $this->getStreamList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getShowStreamTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['show_stream']) ? $data['show_stream'] : '');
        $list = $this->getShowStreamList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStreamDefaultTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['stream_default']) ? $data['stream_default'] : '');
        $list = $this->getStreamDefaultList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getShowAdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['show_ad']) ? $data['show_ad'] : '');
        $list = $this->getShowAdList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getShowVipTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['show_vip']) ? $data['show_vip'] : '');
        $list = $this->getShowVipList();
        return isset($list[$value]) ? $list[$value] : '';
    }


}
