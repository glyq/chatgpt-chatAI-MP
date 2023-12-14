<?php

namespace app\common\model\robot;

use think\Model;

class Session extends Model
{
    // 表名
    protected $name = 'robot_session';

    public function getListByUser($uid)
    {
        $map['a.status'] = 1;
        $map['a.user_id'] = $uid;
        $session = $this
            ->alias('a')
            ->field('a.id,a.assistant_id,if(isnull(a.desc),b.name,a.desc) name,b.icon')
            ->join('robot_assistant b', 'a.assistant_id = b.id')
            ->order('a.updatetime desc')
            ->where($map)
            ->select();
        $session = collection($session)->toArray();
        foreach ($session as $k => $v) {
            $session[$k]['selected'] = false;
            $session[$k]['icon'] = cdnurl($v['icon']);
        }
        return $session;
    }

    public function getActiveSessionByAssistant($uid, $assistantId)
    {
        $map['status'] = 1;
        $map['user_id'] = $uid;
        $map['assistant_id'] = $assistantId;
        $session = $this->order('updatetime desc')->where($map)->find();
        if ($session) {
            $data['updatetime'] = time();
            $this->saveSession($uid, $session['id'], $data);
        }
        return $session;
    }

    public function saveSession($uid, $id, $data)
    {
        $id && $map['id'] = $id;
        $map['user_id'] = $uid;
        $res = $this->where($map)->update($data);
        return $res;
    }

    public function createSession($uid, $assistantId)
    {
        $data['user_id'] = $uid;
        $data['assistant_id'] = $assistantId;
        $data['status'] = 1;
        $data['createtime'] = time();
        $data['updatetime'] = time();
        $this->insert($data);
        $data['id'] = $this->getLastInsID();
        return $data;
    }

}
