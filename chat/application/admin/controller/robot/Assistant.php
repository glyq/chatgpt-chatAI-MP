<?php

namespace app\admin\controller\robot;

use app\common\controller\Backend;
use think\Db;
use think\exception\PDOException;
use think\exception\ValidateException;

/**
 * 助手列管理
 *
 * @icon fa fa-circle-o
 */
class Assistant extends Backend
{

    /**
     * Assistant模型对象
     * @var \app\common\model\robot\Assistant
     */
    protected $model = null;
    protected $searchFields = 'id,name';
    protected $selectpageFields = "id,name,desc";


    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\common\model\robot\Assistant;
        $this->channelassistant = new \app\common\model\robot\Channelassistant();
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
        $this->relationSearch = true;
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage

            if (isset($_REQUEST['keyField'])) {
                if($channelId = $_REQUEST['custom']['channel_id']){
                    $relation = $this->channelassistant->getRelationAssistant($channelId);
                    $relationId = array_column($relation,'assistant_id');
                    unset($_REQUEST['custom']['channel_id']);
                    $relationId && $_REQUEST['custom']['id'] = ['not in',$relationId];


                    $cate = $this->channelcate->getCateListByChannel($channelId);
                    $cateId = array_column($cate,'id');
                    if($cateId){
                        if(!isset($_REQUEST['custom']['cate_id'])){
                            $_REQUEST['custom']['cate_id'] = ['in',$cateId];
                        }
                    }else{
                        $_REQUEST['custom']['cate_id'] = 0;
                    }
                }
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                    ->with(['cate'])
                    ->where($where)
                    ->order($sort, $order)
                    ->paginate($limit);

            foreach ($list as $row) {
                
                $row->getRelation('cate')->visible(['name']);
            }

            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     *
     * @return string
     * @throws \think\Exception
     */
    public function add()
    {
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);


        if ($this->dataLimit && $this->dataLimitFieldAutoFill) {
            $params[$this->dataLimitField] = $this->auth->id;
        }
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }

            $keywords = json_decode($params['keywords'],true);
            if(!$keywords){
                $this->error(__('请检查prompt参数设置，不能为空!'));
            }
            $i = $j= 0;
            foreach ($keywords as $k=>$v){
                if(!$v['tag'] || !$v['title'] || !$v['type']){
                    $this->error(__('请检查prompt参数设置，标签、标题、输入类型必填!'));
                }


                $flag = strpos($params['template'],$v['tag']);
                if($flag === false){
                    $this->error(__('请检查prompt文本设置，标签[['.$v['tag'].']]在prompt文本中不存在！'));
                }

                if(($v['type'] == 'slider')){
                    $v['val'] = (int)$v['val'];
                    if($v['val'] > 1000){
                        $keywords[$k]['val'] = 1000;
                    }
                    if($v['val'] < 50){
                        $keywords[$k]['val'] = 50;
                    }
                    if(!$v['val']){
                        $keywords[$k]['val'] = 200;
                    }
                }

                if($v['type'] == 'textarea'){
                    $i++;
                }
                $j++;
            }

            if($i != 1){
                $this->error(__('请检查prompt参数设置，输入类型必须有且仅有一项为文本区'));
            }

            if($j > 6){
                $this->error(__('请检查prompt参数设置，prompt参数最多设置6项'));
            }

            $params['keywords'] = json_encode($keywords,JSON_UNESCAPED_UNICODE);

            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }


    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $adminIds = $this->getDataLimitAdminIds();
        if (is_array($adminIds) && !in_array($row[$this->dataLimitField], $adminIds)) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->preExcludeFields($params);
        $result = false;
        Db::startTrans();
        try {
            //是否采用模型验证
            if ($this->modelValidate) {
                $name = str_replace("\\model\\", "\\validate\\", get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }


            $keywords = json_decode($params['keywords'],true);

            if(!$keywords){
                $this->error(__('请检查prompt参数设置，不能为空!'));
            }

            $i = $j= 0;
            foreach ($keywords as $k=>$v){
                if(!$v['tag'] || !$v['title'] || !$v['type']){
                    $this->error(__('请检查prompt参数设置，标签、标题、输入类型必填!'));
                }


                $flag = strpos($params['template'],$v['tag']);
                if($flag === false){
                    $this->error(__('请检查prompt文本设置，标签[['.$v['tag'].']]在prompt文本中不存在！'));
                }

                if(($v['type'] == 'slider')){
                    $v['val'] = (int)$v['val'];
                    if($v['val'] > 1000){
                        $keywords[$k]['val'] = 1000;
                    }
                    if($v['val'] < 50){
                        $keywords[$k]['val'] = 50;
                    }
                    if(!$v['val']){
                        $keywords[$k]['val'] = 200;
                    }
                }

                if($v['type'] == 'textarea'){
                    $i++;
                }
                $j++;
            }

            if($i != 1){
                $this->error(__('请检查prompt参数设置，输入类型必须有且仅有一项为文本区'));
            }

            if($j > 6){
                $this->error(__('请检查prompt参数设置，prompt参数最多设置6项'));
            }

            $params['keywords'] = json_encode($keywords,JSON_UNESCAPED_UNICODE);

            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

}
