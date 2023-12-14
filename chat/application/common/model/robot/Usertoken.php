<?php

namespace app\common\model\robot;

use think\Model;


class Usertoken extends Model
{


    // 表名
    protected $name = 'robot_user_token';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];


    public function createUsertoken($userId, $token)
    {
        $tokenData['user_id'] = $userId;
        $tokenData['token'] = $token;
        $tokenData['createtime'] = time();
        $res = self::insert($tokenData);
        return $res;
    }


}
