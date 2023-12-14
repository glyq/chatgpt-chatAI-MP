<?php

namespace app\common\model\robot;

use think\Model;


class Creation extends Model
{

    

    

    // 表名
    protected $name = 'robot_creation';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'channel_id_text',
        'time_text',
        'stream_text',
        'rate_text',
        'platform_text'
    ];



    public function getChannelIdList()
    {
        $map['status'] = 1;
        $field = ['id','name'];
        $channel = new Channel();
        $list = $channel->getList($map,$field);
        $statusList = [];
        foreach ($list as $k=>$v){
            $statusList[$v['id']] = $v['name'];
        }
        return $statusList;
    }

    public function getInfo($id,$uid){
        $map['id'] = $id;
        $map['user_id'] = $uid;
        $creation = $this->where($map)->find()->toArray();
        return $creation;
    }

    public function saveCreation($id,$uid,$data){
        $map['id'] = $id;
        $map['user_id'] = $uid;
        $res = $this->where($map)->update($data);
        return $res;
    }

    public function getList($uid,$sessionId=0,$assistantId=0){
        $map['user_id'] = $uid;
        $sessionId && $map['session_id'] = $sessionId;
        $assistantId && $map['assistant_id'] = $assistantId;
        $list = $this
            ->alias('a')
            ->field('a.id,b.name assistant_name,a.input question,a.msg anwser,a.rate')
            ->order('id desc')
            ->limit(30)
            ->join('robot_assistant b', 'a.assistant_id = b.id')
            ->where($map)
            ->select();
        $list = collection($list)->toArray();
        return $list;
    }

    public function getStreamList()
    {
        return ['2' => __('Stream 2'), '1' => __('Stream 1')];
    }

    public function getRateList()
    {
        return ['0' => __('Rate 0'), '1' => __('Rate 1'), '2' => __('Rate 2'), '3' => __('Rate 3'), '4' => __('Rate 4'), '5' => __('Rate 5')];
    }

    public function getPlatformList()
    {
        return ['wx' => __('Platform wx'), 'pc' => __('Platform pc')];
    }


    public function getChannelIdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['channel_id']) ? $data['channel_id'] : '');
        $list = $this->getChannelIdList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTimeTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['time']) ? $data['time'] : '');
        return is_numeric($value) ? date("Y-m-d H:i:s", $value) : $value;
    }


    public function getStreamTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['stream']) ? $data['stream'] : '');
        $list = $this->getStreamList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getRateTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['rate']) ? $data['rate'] : '');
        $list = $this->getRateList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getPlatformTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['platform']) ? $data['platform'] : '');
        $list = $this->getPlatformList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    protected function setTimeAttr($value)
    {
        return $value === '' ? null : ($value && !is_numeric($value) ? strtotime($value) : $value);
    }


    public function channel()
    {
        return $this->belongsTo('Channel', 'channel_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function assistant()
    {
        return $this->belongsTo('Assistant', 'assistant_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
