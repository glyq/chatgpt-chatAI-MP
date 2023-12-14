<?php

namespace app\admin\controller\robot;

use app\common\controller\Backend;

/**
 * 
 *
 * @icon fa fa-circle-o
 */
class Creation extends Backend
{

    /**
     * Creation模型对象
     * @var \app\common\model\robot\Creation
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\robot\Creation;
        $this->view->assign("channelIdList", $this->model->getChannelIdList());
        $this->view->assign("streamList", $this->model->getStreamList());
        $this->view->assign("rateList", $this->model->getRateList());
        $this->view->assign("platformList", $this->model->getPlatformList());
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
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['channel','assistant'])
                    ->where($where)
                ->where('channel.status','1')
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('channel')->visible(['name']);
				$row->getRelation('assistant')->visible(['name']);
                $inputData = json_decode($row->getData('input'),true);
                $input = '';
                foreach ($inputData as $k=>$v){
                    $input .= $v['title'].':'.$v['val']."\n";
                }
                $row->data('input',$input);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 详情
     */
    public function detail($ids)
    {
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->request->isAjax()) {
            $this->success("Ajax请求成功", null, ['id' => $ids]);
        }
        $arr = $row->toArray();
        $inputData = json_decode($arr['input'],true);
        $input = '';
        foreach ($inputData as $k=>$v){
            $input .= $v['title'].':'.$v['val'];
        }
        $arr['input'] = $input;
        $arr['stream'] = $arr['stream_text'];
        $arr['platform'] = $arr['platform_text'];
        unset($arr['stream_text'],$arr['platform_text'],$arr['channel_id_text'],$arr['time_text'],$arr['rate_text']);
        $this->view->assign("row", $arr);
        return $this->view->fetch();
    }

}
