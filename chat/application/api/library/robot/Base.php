<?php
namespace app\api\library\robot;
use think\Log;
class Base {

    protected function _curl($url,$post=0,$param=[],$header=[],$callback=[],$timeout=60){


        $oCurl = curl_init();
        Log::record('curl-request:'.$url.var_export($param,true));
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        if($post){
            curl_setopt($oCurl, CURLOPT_POST, 1); //post提交方式
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, $param);
        }
        curl_setopt($oCurl, CURLOPT_TIMEOUT, $timeout);
        if ($header) {
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, $header);
        }
        if($callback){
            curl_setopt($oCurl, CURLOPT_WRITEFUNCTION,
                $callback
            );
        }

        $re = curl_exec($oCurl);
        Log::record('curl-response:'.var_export($re,true));
        curl_close($oCurl);
        return $re;
    }


}