<?php

namespace app\common\model\robot;

use think\Model;
use traits\model\SoftDelete;

class Assistant extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'robot_assistant';

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


    public function getAssistantInfoByChannel($channelId, $id = 0)
    {
        $map['a.status'] = 1;
        $id && $map['a.id'] = $id;
        $map['b.channel_id'] = $channelId;

        $assistantInfo = $this
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,a.keywords,a.template')
            ->join('robot_channel_assistant b', 'a.id = b.assistant_id')
            ->order('b.weigh desc')
            ->where($map)
            ->find()
            ->toArray();

        if ($assistantInfo) {
            $assistantInfo['keywords'] = json_decode($assistantInfo['keywords'], true);
            $assistantInfo['icon'] = cdnurl($assistantInfo['icon']);
        }

        return $assistantInfo;
    }

    public function getAssistantListByChannel($channelId, $cateId)
    {
        $map['a.status'] = 1;
        $map['a.cate_id'] = $cateId;
        $map['b.channel_id'] = $channelId;
        $assistantList = $this
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,a.appid,a.apppath')
            ->join('robot_channel_assistant b', 'a.id = b.assistant_id')
            ->order('b.weigh desc')
            ->where($map)
            ->select();

        foreach ($assistantList as $k => $v) {
            $assistantList[$k]['icon'] = cdnurl($v['icon']);
        }
        return $assistantList;
    }

    public function getCateAssistantListByChannel($channelId)
    {
        $map['a.status'] = 1;
        $map['b.status'] = 1;
        $map['c.channel_id'] = $channelId;
        $map['d.channel_id'] = $channelId;
        $res = $this
            ->alias('a')
            ->field('a.id,a.name,a.icon,a.desc,b.name cate,a.appid,a.apppath,a.cate_id')
            ->join('robot_cate b', 'a.cate_id = b.id')
            ->join('robot_channel_cate c', 'b.id = c.cate_id')
            ->join('robot_channel_assistant d', 'a.id = d.assistant_id')
            ->order('c.weigh desc,d.weigh desc')
            ->where($map)
            ->select();
        foreach ($res as $k => $v) {
            $data[$v['cate_id']]['label'] = $v['cate'];
            $data[$v['cate_id']]['labelshort'] = mb_substr($v['cate'], 0, 1);
            $data[$v['cate_id']]['children'][] = [
                'id' => $v['id'],
                'label' => $v['name'],
                'icon' => cdnurl($v['icon']),
                'desc' => $v['desc'],
                'appid' => $v['appid'] ?: '',
                'apppath' => $v['apppath'] ?: '',
            ];

        }
        $cateAssistant = array_values($data);
        return $cateAssistant;
    }

    public function getAssistantByUser($uid)
    {
        $map['b.user_id'] = $uid;
        $res = $this
            ->alias('a')
            ->field('a.id,a.name,a.icon')
            ->join('robot_creation b', 'a.id = b.assistant_id')
            ->order('b.createtime desc')
            ->group('a.id')
            ->limit(100)
            ->where($map)
            ->select();
        $data = array_slice($res, 0, 8);

        foreach ($data as $k => $v) {
            $data[$k]['icon'] = cdnurl($v['icon']);
        }
        return $data;
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


    public function cate()
    {
        return $this->belongsTo('Cate', 'cate_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
