<?php
namespace app\api\library\robot;

use think\Cache;
use think\Log;

class WeChat extends Base{

    public function jscode2session($code,$options){
        $data['appid'] = $options['third_appkey'];
        $data['secret'] = $options['third_appsecret'];
        $data['js_code'] = $code;
        $data['grant_type'] = 'authorization_code';
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $res = $this->_curl($url.'?'.http_build_query($data));
        return $res;
    }

    public function msgSecCheck($content,$openid,$options){
        $accessToken = $this->_getAccessToken($options);
        $url = 'https://api.weixin.qq.com/wxa/msg_sec_check?access_token='.$accessToken;
        $param['content'] = $content;
        $param['version'] = 2;
        $param['scene'] = 1;
        $param['openid'] = $openid;
        $header = [
            'Content-Type: application/json;charset=utf-8'
        ];
        $params = json_encode($param,JSON_UNESCAPED_UNICODE);
        $res = $this->_curl($url,1,$params,$header);
        Log::record('_msgSecCheck:'.$res, 'ERR');
        $data = json_decode($res,true);
        if($data['errcode'] == 0){
            return $data['result']['label'];
        }
        return 100;
    }


    public function getQrCode($scene,$page,$options){
        $accessToken = $this->_getAccessToken($options);
        $url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$accessToken;
        $param['scene'] = $scene;
        $param['page'] = $page;
        $header = [
            'Content-Type: application/json;charset=utf-8'
        ];
        $params = json_encode($param,JSON_UNESCAPED_UNICODE);
        $res = $this->_curl($url,1,$params,$header);
        Log::record('getQrCode:'.$res, 'ERR');

        return $res;
    }

    private function _getAccessToken($options){
        $cacheKey = 'WX_ACCESS_TOKEN_'.$options['third_appkey'];
        $token =  Cache::tag('robot')->get($cacheKey);
        if($token){
            return $token;
        }
        $param['appid'] = $options['third_appkey'];
        $param['secret'] = $options['third_appsecret'];
        $param['grant_type'] = 'client_credential';
        $url = 'https://api.weixin.qq.com/cgi-bin/token';
        $header = [
            'Content-Type: application/json'
        ];
        $url .= '?'.http_build_query($param);
        $res = $this->_curl($url,0,[],$header);
        $data = json_decode($res,true);
        $accessToken = $data['access_token'];
         Cache::tag('robot')->set($cacheKey,$accessToken,3600);
        return $accessToken;
    }
}