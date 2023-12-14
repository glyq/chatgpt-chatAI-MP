<?php

namespace app\common\model\robot;

use think\Model;

class Vip extends Model
{
    // 表名
    protected $name = 'robot_vip';

    public function getInfo($uid)
    {
        $map['user_id'] = $uid;
        $vip = $this->where($map)->find();
        return $vip;
    }

    public function useNum($uid, $num = 1)
    {
        $map['user_id'] = $uid;
        $res = $this->where($map)->setDec('num', $num);
        return $res;
    }

    public function addNum($uid, $num = 1)
    {
        $map['user_id'] = $uid;
        $res = $this->where($map)->setInc('num', $num);
        return $res;
    }

    public function addVip($uid, $num)
    {
        $vipInfo = $this->getInfo($uid);
        if ($vipInfo) {
            return $this->addNum($uid, $num);
        }
        $data['user_id'] = $uid;
        $data['num'] = $num;
        $data['createtime'] = time();
        return $this->insert($data);
    }

}
