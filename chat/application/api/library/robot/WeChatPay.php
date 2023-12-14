<?php
namespace app\api\library\robot;

use GuzzleHttp\Exception\RequestException;
use WechatPay\GuzzleMiddleware\WechatPayMiddleware;
use WechatPay\GuzzleMiddleware\Util\PemUtil;
use GuzzleHttp\HandlerStack;
use WeChatPay\Formatter;
use WeChatPay\Crypto\Rsa;
use think\Env;

class WeChatPay extends Base{
    public function getPayOptions($orderId,$openid,$price,$appid,$body,$type=1){
        $notifyUrl = Env::get('yungouos.notify_url');
        if($type == 1){
            $mchId = Env::get('yungouos.mch_id');
            $data = [
                'out_trade_no' => $orderId,
                'total_fee' => $price,
                'mch_id' => $mchId,
                'body' => $body,
                'open_id' => $openid,
                'app_id' => $appid,
            ];
            $data['sign'] = $this->_getPaySign($data);
            $data['notify_url'] = $notifyUrl;
            $url = 'https://api.pay.yungouos.com/api/pay/wxpay/v3/minAppPay';
            $result = $this->_curl($url,1,http_build_query($data));

            $result = json_decode($result,true);
            $res = [];
            if(isset($result['code']) && ($result['code']==0) && $result['data']){
                $res = $result['data'];
            }
        }

        if($type == 2){
            $res = [
                'order_id' => $orderId,
                'total_fee' => $price,
                'body' => $body,
                'notify_url' => $notifyUrl,
                'title' => $body,
            ];
        }

        if($type == 3){
            $merchantId = Env::get('wechatpay.mch_id'); // 商户号
            $merchantSerialNumber = Env::get('wechatpay.serial_number'); // 商户API证书序列号
            $merchantPrivateKey = PemUtil::loadPrivateKey(ROOT_PATH.Env::get('wechatpay.apiclient_key')); // 商户私钥文件路径
            $wechatpayCertificate = PemUtil::loadCertificate('file://'.ROOT_PATH.Env::get('wechatpay.download_key')); // 微信支付平台证书文件路径
            $wechatpayMiddleware = WechatPayMiddleware::builder()
                ->withMerchant($merchantId, $merchantSerialNumber, $merchantPrivateKey) // 传入商户相关配置
                ->withWechatPay([ $wechatpayCertificate ]) // 可传入多个微信支付平台证书，参数类型为array
                ->build();
            $stack = \GuzzleHttp\HandlerStack::create();
            $stack->push($wechatpayMiddleware, 'wechatpay');
            $client = new \GuzzleHttp\Client(['handler' => $stack]);

            try {
                $date = time() + 300;
                $resp = $client->request(
                    'POST',
                    'https://api.mch.weixin.qq.com/v3/pay/transactions/jsapi', //请求URL
                    [
                        // JSON请求体
                        'json' => [
                            "time_expire" => date('Y-m-d',$date)."T".date('H:i:s',$date)."+08:00",
                            "amount" => [
                                "total" => (int)($price*100),
                                "currency" => "CNY",
                            ],
                            "mchid" => $merchantId,
                            "description" => $body,
                            "notify_url" => Env::get('wechatpay.notify_url'),
                            "payer" => [
                                "openid" => $openid,
                            ],
                            "out_trade_no" => $orderId,
                            "appid" => $appid,

                        ],
                        'headers' => [ 'Accept' => 'application/json' ]
                    ]
                );
                $statusCode = $resp->getStatusCode();
                if ($statusCode == 200) { //处理成功
                    $pre = json_decode($resp->getBody()->getContents(),true);

                    $merchantPrivateKeyFilePath = 'file://'.ROOT_PATH.Env::get('wechatpay.apiclient_key');
                    $merchantPrivateKeyInstance = Rsa::from($merchantPrivateKeyFilePath);

                    $res = [
                        'appId'     => $appid,
                        'timeStamp' => (string)Formatter::timestamp(),
                        'nonceStr'  => Formatter::nonce(),
                        'package'   => 'prepay_id='.$pre['prepay_id'],
                    ];
                    $res += ['paySign' => Rsa::sign(
                        Formatter::joinedByLineFeed(...array_values($res)),
                        $merchantPrivateKeyInstance
                    ), 'signType' => 'RSA'];

                } else if ($statusCode == 204) { //处理成功，无返回Body
                    $res = [];
                }

            } catch (RequestException $e) {
                $res = [];
            }



        }

        return $res;

    }

    private function _getPaySign($data)
    {
        $key = Env::get('yungouos.key');
        ksort($data);
        $sign = strtoupper(md5(urldecode(http_build_query($data)).'&key='.$key));
        return $sign;

    }
}