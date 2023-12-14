<?php

namespace app\admin\controller\robot;

use app\common\controller\Backend;

/**
 * 分类
 *
 * @icon fa fa-circle-o
 */
class Cate extends Backend
{

    /**
     * Cate模型对象
     * @var \app\common\model\robot\Cate
     */
    protected $model = null;
    protected $searchFields = 'id,name';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\robot\Cate;
        $this->channelcate = new \app\common\model\robot\Channelcate();
        $this->view->assign("statusList", $this->model->getStatusList());
    }



    /**
     * 默认生成的控制器所继承的父类中有index/add/edit/del/multi五个基础方法、destroy/restore/recyclebin三个回收站方法
     * 因此在当前控制器中可不用编写增删改查的代码,除非需要自己控制这部分逻辑
     * 需要将application/admin/library/traits/Backend.php中对应的方法复制到当前控制器,然后进行修改
     */


    /**
     * 查看
     */
    public function index()
    {
        //当前是否为关联查询
        $this->relationSearch = false;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage


            if (isset($_REQUEST['keyField'])) {
                if(isset($_REQUEST['custom']['channel_id']) && ($channelId = $_REQUEST['custom']['channel_id'])){

                    $cate = $this->channelcate->getCateListByChannel($channelId);
                    $cateId = array_column($cate,'id');
                    $cateId && $_REQUEST['custom']['id'] = ['not in',$cateId];
                    unset($_REQUEST['custom']['channel_id']);
                }
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id','name','status','createtime','updatetime']);
                
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
