<?php

namespace app\api\controller\robot;

use app\common\model\robot\Cate as cates;
use app\common\model\robot\Assistant;

class Cate extends Base
{

    private $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new Assistant();
    }

    /**
     * 分类列表
     */
    public function cateList()
    {
        $model = new cates();
        $cates = $model->getCateListByChannel($this->channel['id']);
        $this->response(0, $cates);
    }

    /**
     * 助手详情
     */
    public function assistantInfo()
    {
        $id = $this->request->get('assistant_id');
        $assistantInfo = $this->model->getAssistantInfoByChannel($this->channel['id'], $id);
        $assistantInfo['stream']['show'] = (int)$this->channel['show_stream'];
        $assistantInfo['stream']['default'] = $this->channel['stream_default'] ? true : false;
        $this->response(0, $assistantInfo);
    }

    /**
     * 助手列表按分类查询
     */
    public function assistantList()
    {
        $cateId = $this->request->get('cate_id');
        $assistantList = $this->model->getAssistantListByChannel($this->channel['id'], $cateId);
        $this->response(0, $assistantList);
    }

    /**
     * 所有助手列表按分类分组
     */
    public function cateAssistant()
    {
        $cateAssistant = $this->model->getCateAssistantListByChannel($this->channel['id']);
        $this->response(0, $cateAssistant);
    }

    /**
     * 我的常用
     */
    public function myAssistant()
    {
        $uid = $this->uid;
        if (!$uid) {
            $this->response(0, []);
        }
        $data = $this->model->getAssistantByUser($uid);
        $this->response(0, $data);
    }


}
