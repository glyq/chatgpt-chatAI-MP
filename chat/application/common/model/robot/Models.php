<?php

namespace app\common\model\robot;

use think\Model;
use traits\model\SoftDelete;

class Models extends Model
{

    use SoftDelete;


    // 表名
    protected $name = 'robot_model';

    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'integer';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'company_text',
        'status_text'
    ];


    public function getCompanyList()
    {
        return ['Wenxin' => __('百度'), 'Ali' => __('阿里'), 'Xunfei' => __('科大讯飞'), 'Ai360' => __('360智脑'), 'Chatglm' => __('智谱ai'),"Chatgpt"=>__('OpenAi'),"Api2d"=>__('Api2d')];
    }

    public function getModelList()
    {
        $model = [
            'Wenxin' => [
                'ERNIE-Bot 4.0',
                'ERNIE-Bot-8K',
                'ERNIE-Bot-turbo',
                'ERNIE-Bot'
            ],
            'Ali' => [
                'qwen-max',
                'qwen-plus',
                'qwen-turbo'
            ],
            'Xunfei' => [
                'generalv1',
                'generalv2',
                'generalv3',
            ],
            'Ai360' => [
                '360GPT_S2_V9'
            ],
            'Chatglm' => [
                'chatglm_turbo',
                'chatglm_pro'
            ],
            'Chatgpt' => [
                'gpt-3.5-turbo',
                'gpt-3.5-turbo-0301',
                'gpt-3.5-turbo-0613',
                'gpt-3.5-turbo-1106',
                'gpt-3.5-turbo-16k',
                'gpt-3.5-turbo-16k-0613',
                'gpt-4',
                'gpt-4-0314',
                'gpt-4-0613',
                'gpt-4-32k',
                'gpt-4-32k-0314',
            ],
            'Api2d' => [
                'gpt-3.5-turbo',
                'gpt-3.5-turbo-0301',
                'gpt-3.5-turbo-0613',
                'gpt-3.5-turbo-1106',
                'gpt-3.5-turbo-16k',
                'gpt-3.5-turbo-16k-0613',
                'gpt-4',
                'gpt-4-0613',
                'gpt-4-vision-preview',
                'gpt-4-1106-preview',
            ]
        ];
        return $model;
    }

    public function getStatusList()
    {
        return ['0' => __('Status 0'), '1' => __('Status 1')];
    }


    public function getCompanyTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['company']) ? $data['company'] : '');
        $list = $this->getCompanyList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getStatusTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }


}
