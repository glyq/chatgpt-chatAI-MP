<?php

namespace app\api\controller\robot;

use think\Cache;
use app\common\model\robot\User as userModel;
use app\common\model\robot\Usertoken;
use app\common\model\robot\Help;
use app\common\model\robot\Order;
use app\common\model\robot\Addlist;
use app\common\model\robot\Notice;
use app\common\model\robot\Buy as Activity;
use app\api\library\robot\WeChat;
use app\api\library\robot\WeChatPay;
use Workerman\Connection\TcpConnection;
use Workerman\Timer;

class User extends Base
{

    private $userModel = null;
    private $activityModel = null;
    private $helpModel = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->userModel = new userModel();
        $this->activityModel = new Activity();
        $this->helpModel = new Help();
    }

    /**
     * 小程序微信登录
     */
    public function wxMiniLogin()
    {
        $param = $this->request->post();
        $code = $param['code'];
        if (!$code) {
            $this->response(1000);
        }
        $weChat = new WeChat();
        $res = $weChat->jscode2session($code, $this->channel);
        $token = md5($res);
        $result = json_decode($res, true);
        $openid = isset($result['openid']) ? $result['openid'] : '';
        $sessionKey = isset($result['session_key']) ? $result['session_key'] : '';
        $unionid = isset($result['unionid']) ? $result['unionid'] : '';
        if (!$openid || !$sessionKey) {
            $this->response(1001);
        }

        $userInfo = $this->userModel->getUserByOpenid($openid);
        $userId = isset($userInfo['id']) ? $userInfo['id'] : 0;

        if (!$userId) {
            $userInfo = $this->userModel->createUser($this->channel['id'], 'wx', $openid, $unionid);
            $userId = $userInfo['id'];
            if (!$userInfo) {
                $this->response(1002);
            }
        }

        $model = new Usertoken();
        $model->createUsertoken($userId, $token);

        $user['accessToken'] = $token;
        $user['userInfo'] = $userInfo;
        $this->response(0, $user);
    }

    /**
     * 剩余次数
     */
    public function getVipInfo()
    {
        if (!$this->uid) {
            $this->response(0, ['nums' => 0, 'free_nums' => 0, 'day_nums' => 0, 'used_nums' => 0, 'show_vip' => 0]);
        }
        $data = $this->_getUserNums();
        if (!$this->channel['show_vip']) {
            $data['show_vip'] = 0;
        }
        $this->response(0, $data);
    }

    /**
     * 活动、任务列表
     */
    public function getBuyList()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $list = $this->activityModel->getListByChannel($this->channel['id']);

        $shareNum = $this->_getTodayNums('share_limit');
        $adNum = $this->_getTodayNums('ad_limit');

        foreach ($list as $k => $v) {
            if (($v['type'] == 4)) {
                if (($adNum >= $v['max_nums'])) {
                    $list[$k]['disabled'] = 1;
                    $adNum = $v['max_nums'];
                }
                $list[$k]['desc'] .= ' 已观看次数（' . $adNum . '/' . $v['max_nums'] . '）';
            }

            if ($v['type'] == 2) {
                $helpList = $this->helpModel->getList($this->uid);
                $count = count($helpList);
                if ($count >= $v['max_nums']) {
                    $list[$k]['disabled'] = 1;
                    $count = $v['max_nums'];
                }
                $list[$k]['desc'] .= ' 已助力人数（' . $count . '/' . $v['max_nums'] . '）';
            }

            if ($v['type'] == 3) {
                $list[$k]['disabled'] = 0;
                $list[$k]['desc'] .= ' 已分享次数（' . $shareNum . '/' . $v['max_nums'] . '）';
            }

            $v['img'] && $list[$k]['img'] = cdnurl($v['img']);

        }

        $this->response(0, $list);
    }


    /**
     * 助力详情
     */
    public function getHelpInfo()
    {
        if (!$this->uid) {
            $this->response(1003);
        }
        $id = $this->request->post('id');
        $info = $this->activityModel->getInfoByChannel($this->channel['id'], 2, $id);
        $limit = $info['max_nums'] ? $info['max_nums'] : 20;

        $helpInfo = $this->helpModel->getList($this->uid);
        $helpInfo = array_slice($helpInfo, 0, $limit);

        $count = count($helpInfo);
        $data['count'] = $count;
        $data['finish'] = 0;
        $data['urls'] = array_column($helpInfo, 'head_img');
        if ($count >= $info['max_nums']) {
            $data['finish'] = 1;
        }
        $this->response(0, $data);
    }

    /**
     * 助力
     */
    public function help()
    {
        $pid = $this->request->post('pid');
        if (!$pid) {
            $this->response(3002);
        }
        if (!$this->uid) {
            $this->response(1003);
        }
        if ($pid == $this->uid) {
            $this->response(3004);
        }

        $help = $this->helpModel->getList(0, $this->uid);
        if (count($help) >= 3) {
            $this->response(3001);
        }
        $pidList = array_column($help, 'pid');
        if (in_array($pid, $pidList)) {
            $this->response(3003);
        }

        $this->helpModel->createHelp($this->channel['id'], $this->uid, $pid);

        $helpList = $this->helpModel->getList($pid);
        $count = count($helpList);

        $activityInfo = $this->activityModel->getInfoByChannel($this->channel['id'], 2);

        if ($count > $activityInfo['max_nums']) {
            $this->response(0);
        }

        if ($count == $activityInfo['max_nums']) {
            $this->_addVip($pid, $activityInfo['add_nums']);
        }

        $this->response(0);
    }


    /**
     * 购买
     */
    public function buy()
    {
        if (!$this->uid) {
            $this->response(1003);
        }

        $id = $this->request->post('id');
        $info = $this->activityModel->getInfoByChannel($this->channel['id'], 1, $id);

        if (!$info || !$info['price']) {
            $this->response(4001);
        }

        $userInfo = $this->userModel->getUserInfo($this->uid);

        $openid = $userInfo['openid'] ?: '';
        if (!$openid) {
            $this->response(4002);
        }

        $orderModel = new Order();
        $orderId = $orderModel->createOrder($this->uid, $this->channel['id'], $openid, $info['price'], $info['add_nums']);

        $pay = new WeChatPay();
        $body = $this->channel['name'] . 'vip';
        $appkey = $this->channel['third_appkey'];
        $type = 2;
        $res = $pay->getPayOptions($orderId,$openid,$info['price'],$appkey,$body,$type);

        $data['type'] = $type;
        $data['data'] = $res;

        $this->response(0, $data);

    }

    /**
     * 看广告、分享新增次数
     */
    public function addNums()
    {
        if (!$this->uid) {
            $this->response(1003);
        }

        $id = $this->request->post('id');

        if (!$id) {
            $list = $this->activityModel->getInfoByChannel($this->channel['id'], 3);
            $key = 'share_limit';
        } else {
            $list = $this->activityModel->getInfoByChannel($this->channel['id'], 0, $id);
            $key = 'ad_limit';
        }
        if (!$list) {
            $this->response(-6);
        }

        $adNum = $this->_getTodayNums($key);
        if ($adNum >= $list['max_nums']) {
            $this->response(5001);
        }
        $this->_setTodayNums($key, $list['add_nums']);

        $model = new Addlist();
        $model->addNums($key, $list['add_nums'], $this->uid, $this->channel['id']);

        $this->response(0);
    }


    /**
     * 服务声明详情
     */
    public function getNotice()
    {
        $id = $this->request->get('id');
        $map['id'] = $id;
        $map['status'] = 1;
        $map['channel_id'] = $this->channel['id'];
        $info = Notice::where($map)->find();
        $this->response(0, $info);
    }

    /**
     * 服务声明列表
     */
    public function getNoticeList()
    {
        $model = new Notice();
        $info = $model->getListByChannel($this->channel['id']);
        $this->response(0, $info);
    }

    /**
     * 小程序是否显示广告
     */
    public function showAd()
    {
        $info['show'] = (int)$this->channel['show_ad'];
        $this->response(0, $info);
    }

    /**
     * pc端登录
     */
    public function confirmPcLogin()
    {
        $scene = $this->request->post('scene');
        $data = Cache::tag('robot')->get($scene);
        if (!$data) {
            $this->response(1002);
        }
        $userInfo = $this->userModel->getUserInfo($this->uid);
        $userInfo['token'] = $this->accessToken;
        $userInfo['head_img'] = cdnurl($userInfo['head_img']);
        $data['userInfo'] = $userInfo;
        $data['status'] = 1;
        Cache::tag('robot')->set($scene, $data, 600);
        $this->response(0);
    }

    /**
     * 获取pc端登录二维码
     */
    public function getQrCode()
    {
        $time = time();
        $scene = md5($time . rand(100000, 999999));
        $type = $this->request->post('type');
        $page = "pages/my/loginPc";
        if ($type == 1) {
            $page = "pages/my/buy";
        }

        $data['create_time'] = time();
        $data['channel_id'] = $this->channel['id'];
        $data['status'] = 0;
        Cache::tag('robot')->set($scene, $data, 600);
        $wechat = new WeChat();
        $result = $wechat->getQrCode($scene, $page, $this->channel);
        $info['img'] = "data:image/jpeg;base64," . base64_encode($result);
        $info['scene'] = $scene;
        $this->response(0, $info);
    }

    /**
     * pc端登录
     */
    public function loginServer($worker)
    {
        $worker->onMessage = function (TcpConnection $connection, $msg) {
            $connection->lastMessageTime = time();
            for ($i = 0; $i < 100; $i++) {
                $data = Cache::tag('robot')->get($msg);
                if (!$data) {
                    break;
                }
                $connection->send(json_encode($data));
                if ($data['status'] == 1) {
                    sleep(1);
                    break;
                }
                sleep(1);
            }
            $connection->close();
        };

        $worker->onWorkerStart = function ($worker) {
            Timer::add(10, function () use ($worker) {
                $time_now = time();
                foreach ($worker->connections as $connection) {
                    // 有可能该connection还没收到过消息，则lastMessageTime设置为当前时间
                    if (empty($connection->lastMessageTime)) {
                        $connection->lastMessageTime = $time_now;
                        continue;
                    }
                    // 上次通讯时间间隔大于心跳间隔，则认为客户端已经下线，关闭连接
                    if ($time_now - $connection->lastMessageTime > 100) {
                        $connection->close();
                    }
                }
            });
        };

        $worker->onClose = function (TcpConnection $connection) {
            echo "connection closed\n";
        };

        $worker->onConnect = function (TcpConnection $connection) {
            $connection->id = rand(100000, 999999);
            echo "new connection " . "\n";
        };
    }




}
