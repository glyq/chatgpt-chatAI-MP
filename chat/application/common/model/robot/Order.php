<?php

namespace app\common\model\robot;

use think\Model;


class Order extends Model
{


    // 表名
    protected $name = 'robot_order';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'channel_id_text',
        'status_text'
    ];


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

    public function createOrder($uid, $channelId, $openid, $price, $addNums)
    {
        $orderId = date('YmdHis') . rand(1000, 9999);
        $order['order_id'] = $orderId;
        $order['user_id'] = $uid;
        $order['channel_id'] = $channelId;
        $order['third_order_id'] = '';
        $order['user'] = $openid;
        $order['status'] = 0;
        $order['createtime'] = time();
        $order['price'] = $price;
        $order['num'] = $addNums;
        $this->insert($order);
        return $orderId;
    }

    public function getStatusList()
    {
        return ['1' => __('Status 1'), '0' => __('Status 0')];
    }


    public function getChannelIdTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['channel_id']) ? $data['channel_id'] : '');
        $list = $this->getChannelIdList();
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
