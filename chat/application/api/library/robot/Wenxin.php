<?php
namespace app\api\library\robot;


use think\Cache;
use think\Exception;
use think\Log;
use ReflectionFunction;

class Wenxin extends Base implements Channel{

    public function chatStream($param,$options,$connection=''){
        $url = '';
        switch ($param['model']){
            case 'ERNIE-Bot':
                $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions';
                break;
            case 'ERNIE-Bot-turbo':
                $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/eb-instant';
                break;
            case 'ERNIE-Bot-8K':
                $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/ernie_bot_8k';
                break;
            case 'ERNIE-Bot 4.0':
                $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions_pro';
                break;
            default:
                break;
        }
        if($options['api_url']){
            $url = $options['api_url'];
        }

        if(!$url){
            return [];
        }

        $url .= '?access_token='.$this->_getWenxinToken($options);

        $header = [
            'Content-Type: application/json',
        ];

        foreach ($param['contents'] as $v){
            $params['messages'][] = ['role'=>$v['role'],'content'=>$v['content']];
        }

        $params['temperature'] = $param['temperature'];
        $params['penalty_score'] = 2;
        $params['stream'] = true;

        $callback = function($oCurl, $data) use($param,$connection) {

            static $result = [];
            static $str = '';
            try {
                Log::record('curl-response-stream-wenxin:'.var_export($data,true));
                $result['code'] = 0;
                $result['tokens'] = 0;
                $result['msg'] = '成功';
                $result['model'] = $param['model'];

                $res = $this->_parseData($data);

                if(isset($res['error_code']) && $res['error_code']){
                    $result['code'] = -1;
                    $result['msg'] = isset($res['error_msg']) ? $res['error_msg'] : '未知错误';
                    $result['data'] = '';
                    return 0;
                }

                if(isset($res['result'])){
                    $str .= $res['result'];
                }
                $result['data'] = $str;

                if(isset($res['is_end']) && ($res['is_end']=='true')){
                    $tokens = isset($res['usage']['total_tokens']) ? $res['usage']['total_tokens'] : 0;
                    $result['tokens'] = $tokens;
                }

                $connection->send(json_encode($result));

            }catch (Exception $exception){
                $result['code'] = -1;
                $result['msg'] = '未知错误';
                $result['tokens'] = 0;
                $result['data'] = $str;
                $result['model'] = $param['model'];
                return 0;
            }
            return strlen($data);
        };

        $this->_curl($url,1,json_encode($params),$header,$callback);

        $func = new ReflectionFunction($callback);
        $data = $func->getStaticVariables();

        return $data['result'];

    }

    public function chat($param,$options){
        $result['model'] = $param['model'];
        $result['tokens'] = 0;

        $url = '';

        switch ($result['model']){
            case 'ERNIE-Bot':
                $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions';
                break;
            case 'ERNIE-Bot-turbo':
                $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/eb-instant';
                break;
            case 'ERNIE-Bot-8K':
                $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/ernie_bot_8k';
                break;
            case 'ERNIE-Bot 4.0':
                $url = 'https://aip.baidubce.com/rpc/2.0/ai_custom/v1/wenxinworkshop/chat/completions_pro';
                break;
            default:
                break;
        }
        if($options['api_url']){
            $url = $options['api_url'];
        }

        if(!$url){
            return [];
        }

        $url .= '?access_token='.$this->_getWenxinToken($options);

        $header = [
            'Content-Type: application/json',
        ];

        foreach ($param['contents'] as $v){
            $params['messages'][] = ['role'=>$v['role'],'content'=>$v['content']];
        }

        $params['stream'] = false;
        $params['temperature'] = $param['temperature'];
        $params['penalty_score'] = 2;
        $res = $this->_curl($url,1,json_encode($params),$header);
        $data = json_decode($res,true);
        $message = isset($data['result']) ? $data['result'] : '';
        $tokens = isset($data['usage']['total_tokens']) ? $data['usage']['total_tokens']: 0;

        if(!$message){
            $result['code'] = isset($data['error_code']) ? $data['error_code'] : '-1';
            $result['data'] = '';
            $result['tokens'] = 0;
            $result['msg'] = isset($data['error_msg']) ? $data['error_msg'] : '未知错误，生成失败';
            return $result;
        }

        $result['data'] = $message;
        $result['code'] = 0;
        $result['msg'] = '成功';
        $result['tokens'] = $tokens;
        return $result;
    }

    private function _getWenxinToken($options){
        $appId = $options['appkey'];
        $appSecret = $options['appsecret'];

        $cacheKey = 'WENXIN_ACCESS_TOKEN';
        $token =  Cache::tag('robot')->get($cacheKey);
        if($token){
            return $token;
        }

        $url = 'https://aip.baidubce.com/oauth/2.0/token?grant_type=client_credentials&client_id='.$appId.'&client_secret='.$appSecret;
        $header = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        $res = $this->_curl($url,1,[],$header);
        $data = json_decode($res,true);
        $token = $data['access_token'];
         Cache::tag('robot')->set($cacheKey,$token,3600*7);
        return $token;
    }

    private function _parseData($data){
        $responseArr = substr($data,6,strlen($data));

        $responseArr = json_decode($responseArr,true);
        if(!$responseArr){
            return [];
        }
        return $responseArr;
    }
}