import md5 from './md5'
import config from './config';

/**
 * 
 * @param {微信支付签名} params 
 * @param {*} key 
 */
function wxPaySign(params, key) {
    const paramsArr = Object.keys(params);
    paramsArr.sort();
    const stringArr = []
    paramsArr.map(key => {
        stringArr.push(key + '=' + params[key]);
    })
    // 最后加上 商户Key
    stringArr.push("key=" + key)
    const string = stringArr.join('&');
    return md5.md5(string).toString().toUpperCase();
}

/**
 * 生成订单号
 * @param {订单号前缀} str 
 */
function getOrderNo(str) {
    let outTradeNo = "";  //订单号
    for (var i = 0; i < 6; i++) //6位随机数，用以加在时间戳后面。
    {
        outTradeNo += Math.floor(Math.random() * 10);
    }
    outTradeNo = str + new Date().getTime() + outTradeNo;  //时间戳，用来生成订单号。
    return outTradeNo;
}

/**
 * 
 * @param {金额} long_data 
 * @param { 可选,格式化金额精度, 即小数点位数. 如: 3 标示保留小数点后三位, 默认为2位} length 
 */
function formatMoney(long_data, length) {
    length = length > 0 && length <= 20 ? length : 2;
    long_data = parseFloat((long_data + "").replace(/[^\d\.-]/g, "")).toFixed(length) + "";
    let l = long_data.split(".")[0].split("").reverse();
    let r = long_data.split(".")[1];
    let t = "";
    for (let i = 0; i < l.length; i++) {
        t += l[i] + ((i + 1) % 3 == 0 && (i + 1) != l.length ? "," : "");
    }
    return t.split("").reverse().join("") + "." + r;
}


/**
 * 发起小程序支付
 */
function toPay(out_trade_no,total_fee,body,notify_url,attach,title,callback) {
    //需要加密的参数
    let data = {
        
        out_trade_no: out_trade_no,
        total_fee: total_fee,
        mch_id: config.mch_id,
        body: body,
        
    }
    //加密参数，支付密钥（登录YunGouOS.com 微信支付-》商户管理 查看）
    let sign = wxPaySign(data, config.key);
    //构造其他小程序跳转参数数据
    data.notify_url = notify_url;
    data.attach = attach;
    data.title = title//收银台显示的顶部标题 xxxx-收银台
    //.... 按照文档 按需添加参数
    //最后我们把签名加入进去
    data.sign = sign;

    //下面开始执行小程序支付
    //接口文档地址：http://open.pay.yungouos.com/#/api/api/pay/wxpay/minPay
    //支付流程：小程序A 点击付款->跳转到 “支付收银” 小程序 -> 自动发起微信支付 ->支付成功后携带支付结果返回小程序A

    uni.openEmbeddedMiniProgram({
        appId: 'wxd9634afb01b983c0',//支付收银小程序的appid 固定值 不可修改
        path: '/pages/pay/pay',//支付页面 固定值 不可修改
        extraData: data,//携带的参数 参考API文档
        success(res) {
            callback(res);
        }, fail(res) {
            callback(res);
        }
    });
}





/**
 * 暴露方法外部调用
 */
export default {
    "wxPaySign": wxPaySign,
    "getOrderNo": getOrderNo,
    "formatMoney": formatMoney,
    "toPay":toPay
}