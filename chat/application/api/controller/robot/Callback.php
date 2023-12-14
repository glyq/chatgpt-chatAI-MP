<?php

namespace app\api\controller\robot;

use think\Cache;
use app\common\model\robot\Order;
use WeChatPay\Crypto\Rsa;
use WeChatPay\Crypto\AesGcm;
use WeChatPay\Formatter;
use think\Log;
use think\Env;

class Callback extends Base
{
    protected $noNeedLogin = ['*'];
    protected $noNeedRight = ['*'];

    public function _initialize()
    {

    }

    /**
     * 支付回调
     */
    public function buySuccess()
    {

        $headers = getallheaders();
        $inWechatpaySignature = isset($headers['Wechatpay-Signature']) ? $headers['Wechatpay-Signature'] : '';
        $inWechatpayTimestamp = isset($headers['Wechatpay-Timestamp']) ? $headers['Wechatpay-Timestamp'] : '';
        $inWechatpayNonce = isset($headers['Wechatpay-Nonce']) ? $headers['Wechatpay-Nonce'] : '';

        if($inWechatpaySignature){
            $inBody = file_get_contents('php://input');
            $apiv3Key = Env::get('wechatpay.key_v3');// 在商户平台上设置的APIv3密钥
            $platformPublicKeyInstance = Rsa::from(file_get_contents(ROOT_PATH.Env::get('wechatpay.download_key')), Rsa::KEY_TYPE_PUBLIC);

            $timeOffsetStatus = 300 >= abs(Formatter::timestamp() - (int)$inWechatpayTimestamp);
            $verifiedStatus = Rsa::verify(
                Formatter::joinedByLineFeed($inWechatpayTimestamp, $inWechatpayNonce, $inBody),
                $inWechatpaySignature,
                $platformPublicKeyInstance
            );
            if(!$timeOffsetStatus || !$verifiedStatus){
                echo 'fail';exit;
            }
            // 转换通知的JSON文本消息为PHP Array数组
            $inBodyArray = (array)json_decode($inBody, true);
            // 使用PHP7的数据解构语法，从Array中解构并赋值变量
            ['resource' => [
                'ciphertext'      => $ciphertext,
                'nonce'           => $nonce,
                'associated_data' => $aad
            ]] = $inBodyArray;
            // 加密文本消息解密
            $inBodyResource = AesGcm::decrypt($ciphertext, $apiv3Key, $nonce, $aad);
            // 把解密后的文本转换为PHP Array数组
            $inBodyResourceArray = (array)json_decode($inBodyResource, true);

            $orderId = isset($inBodyResourceArray['out_trade_no']) ? $inBodyResourceArray['out_trade_no'] : '';
            $sourceOrderId = isset($inBodyResourceArray['transaction_id']) ? $inBodyResourceArray['transaction_id'] : '';
            $lock = Cache::tag('robot')->get('pay_order_' . $orderId);
            if($lock){
                echo 'success';exit;
            }
            $this->_updateOrder($orderId,$sourceOrderId);
            Cache::tag('robot')->set('pay_order_' . $orderId, 1, 86400);
            Log::record('payMsg:' . var_export($inBodyResourceArray, true), 'ERR');

            echo 'success';exit;
        }else{
            Log::record('payMsg:' . var_export($_POST, true), 'ERR');
            $data = $_POST;
            $orderId = $data['outTradeNo'];
            $sourceOrderId = $data['orderNo'];

            $lock = Cache::tag('robot')->get('pay_order_' . $orderId);
            if ($lock) {
                echo 'SUCCESS';
                exit;
            }
            Cache::tag('robot')->set('pay_order_' . $orderId, 1, 86400);
            $status = $data['code'];
            $sign = $data['sign'];

            $signArr = [
                'code' => $data['code'],
                'orderNo' => $data['orderNo'],
                'outTradeNo' => $data['outTradeNo'],
                'payNo' => $data['payNo'],
                'money' => $data['money'],
                'mchId' => $data['mchId'],
            ];

            ksort($signArr);
            $postSign = strtoupper(md5(urldecode(http_build_query($signArr)) . '&key='.Env::get('yungouos.key')));
            if ($sign != $postSign) {
                echo 'error';
                exit;
            }

            if ($status != 1) {
                echo 'SUCCESS';
                exit;
            }

            $this->_updateOrder($orderId,$sourceOrderId);
            echo 'success';
            exit;
        }

    }


    private function _updateOrder($orderId,$sourceOrderId)
    {
        if(!$orderId || !$sourceOrderId){
            return;
        }
        Order::where(['order_id' => $orderId])->update(['status' => 1, 'updatetime' => time(), 'third_order_id' => $sourceOrderId]);
        $order = Order::where(['order_id' => $orderId])->find();

        $this->_addVip($order['user_id'], $order['num']);
    }


}