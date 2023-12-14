<?php

namespace app\admin\controller\robot;

use app\common\controller\Backend;



/**
 * 渠道应用
 *
 * @icon fa fa-circle-o
 */
class Channel extends Backend
{
    /**
     * Channel模型对象
     * @var \app\common\model\robot\Channel
     */
    protected $model = null;
    protected $searchFields = 'id,name';


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\robot\Channel;
        $this->view->assign("streamList", $this->model->getStreamList());
        $this->view->assign("statusList", $this->model->getStatusList());
        $this->view->assign("showStreamList", $this->model->getShowStreamList());
        $this->view->assign("streamDefaultList", $this->model->getStreamDefaultList());
        $this->view->assign("showAdList", $this->model->getShowAdList());
        $this->view->assign("showVipList", $this->model->getShowVipList());
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
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                $row->visible(['id','name','desc','free_num','show_stream','stream','status','show_vip','show_ad','createtime','updatetime']);
                
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

}
