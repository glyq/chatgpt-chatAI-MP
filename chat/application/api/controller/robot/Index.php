<?php

namespace app\api\controller\robot;

use app\common\model\robot\Banner;
use app\common\model\robot\Msg;


class Index extends Base
{
    /**
     * 小程序轮播图列表
     */
    public function getList()
    {
        $model = new Banner();
        $map['status'] = 1;
        $map['channel_id'] = $this->channel['id'];
        $banner = $model->getList($map);
        $data['swiper'] = $banner;
        $this->response(0, $data);
    }


    /**
     * 小程序通知消息列表
     */
    public function getMsg()
    {
        $map['status'] = 1;
        $map['channel_id'] = $this->channel['id'];
        $map['position'] = $this->request->get('position');
        $model = new Msg();
        $msg = $model->getInfo($map);
        $this->response(0, $msg);
    }

    /**
     * 获取应用信息
     */
    public function getInfo()
    {
        $data['name'] = $this->channel['name'];
        $data['desc'] = $this->channel['desc'];
        $this->response(0, $data);
    }


}
