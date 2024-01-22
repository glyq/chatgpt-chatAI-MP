<?php

namespace app\admin\controller\robot;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Model extends Backend
{

    /**
     * Models模型对象
     * @var \app\common\model\robot\Models
     */
    protected $model = null;
    protected $selectpageFields = ['id','model_tag','company',];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\robot\Models;
        $this->channelmodel = new \app\common\model\robot\Channelmodel();

        $this->view->assign("companyList", $this->model->getCompanyList());
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
                if($channelId = $_REQUEST['custom']['channel_id']){
                    $model = $this->channelmodel->getModelListByChannel($channelId);
                    $modelId = array_column($model,'id');
                    $modelId && $_REQUEST['custom']['id'] = ['not in',$modelId];
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
                $row->visible(['id','company','model_tag','model_class','status','createtime','updatetime']);
                
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    public function getCompanyList()
    {
        $list = $this->model->getCompanyList();
        foreach ($list as $k=>$v){
            $data[] = [
                'value' => $k,
                'name' => $v
            ];
        }
        $this->success('', '', $data);
    }

    public function getModelList()
    {
        $company = $this->request->request('company');
        $list = $this->model->getModelList();
        $modelList = isset($list[$company]) ? $list[$company] : [];
        $data = [];
        foreach ($modelList as $k=>$v){
            $data[] = [
                'value' => $v,
                'name' => $v,
            ];
        }
        $this->success('', '', $data);
    }

}
