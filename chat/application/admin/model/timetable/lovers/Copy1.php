<?php

namespace app\admin\model\timetable\lovers;

use think\Model;


class Copy1 extends Model
{

    

    

    // 表名
    protected $name = 'timetable_lovers_copy1';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = false;

    // 定义时间戳字段名
    protected $createTime = false;
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'starttime_text',
        'endtime_text',
        'love_sort_text'
    ];
    

    
    public function getLoveSortList()
    {
        return ['0' => __('Love_sort 0'), '1' => __('Love_sort 1'), '2' => __('Love_sort 2')];
    }


    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['starttime']) ? $data['starttime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getEndtimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['endtime']) ? $data['endtime'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getLoveSortTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['love_sort']) ? $data['love_sort'] : '');
        $list = $this->getLoveSortList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setStarttimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }

    protected function setEndtimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


}
