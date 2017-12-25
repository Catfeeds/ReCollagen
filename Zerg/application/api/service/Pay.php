<?php

namespace app\api\service;

use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\api\model\User as UserModel;
use app\api\model\Product as ProductModel;
use app\api\model\ProductOption as ProductOption;

use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use think\Loader;
use think\Log;
use think\Db;

//Loader::import('WxPay.WxPay', EXTEND_PATH, '.Data.php');
Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');


class Pay
{
    private $orderNo;
    private $orderID;
//    private $orderModel;

    function __construct($orderID)
    {
        if (!$orderID)
        {
            throw new Exception('订单号不允许为NULL');
        }
        $this->orderID = $orderID;
    }

    public function pay()
    {
        $this->checkOrderValid();
        $order = new Order();
        $status = $order->checkOrderStock($this->orderID);
        if (!$status['pass'])
        {
            return $status;
        }
        return $this->makeWxPreOrder($status['orderPrice']);
        //        $this->checkProductStock();
    }

    // 构建微信支付订单信息
    private function makeWxPreOrder($totalPrice)
    {
        $openid = Token::getCurrentTokenVar('openid');

        if (!$openid)
        {
            throw new TokenException();
        }
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNo);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice * 100);
        $wxOrderData->SetBody('零食商贩');
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url(config('secure.pay_back_url'));

        return $this->getPaySignature($wxOrderData);
    }

    //向微信请求订单号并生成签名
    private function getPaySignature($wxOrderData)
    {
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);
        // 失败时不会返回result_code
        if($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] !='SUCCESS'){
            Log::record($wxOrder,'error');
            Log::record('获取预支付订单失败','error');
//            throw new Exception('获取预支付订单失败');
        }
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    private function recordPreOrder($wxOrder){
        // 必须是update，每次用户取消支付后再次对同一订单支付，prepay_id是不同的
        OrderModel::where('id', '=', $this->orderID)
            ->update(['prepay_id' => $wxOrder['prepay_id']]);
    }

    // 签名
    private function sign($wxOrder)
    {
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;
        unset($rawValues['appId']);
        return $rawValues;
    }

    /**
     * 订单状态检测
     */
    private function checkOrderValid()
    {
        $order = OrderModel::where('order_id', '=', $this->orderID)
            ->find();
        if (!$order)
        {
            throw new OrderException();
        }
//        $currentUid = Token::getCurrentUid();
        if(!Token::isValidOperate($order->uid))
        {
            throw new TokenException(
                [
                    'msg' => '订单与用户不匹配',
                    'errorCode' => 10003
                ]);
        }
        if($order->status != 1){
            throw new OrderException([
                'msg' => '订单状态异常',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }
        $this->orderNo = $order->order_no;
        return true;
    }

    /**
     * 支付订单
     */
    public function orderPay(){
        $this->checkOrderValid();
        //检测库存
        $service = new OrderService();
        $status = $service->checkOrderStock($this->orderID);

        $uid  = Token::getCurrentUid();
        $user = UserModel::get($uid);
        $order = OrderModel::get($this->orderID);
        $mainAccountNeedPay   = $order['mainGoodsPrice'];
        $secondAccountNeedPay = $order['otherGoodsPrice'] + $order['shippingPrice'];

        //判断用户主账户和小金库的余额是否充足
        $this->getUserAccountStatus($user,$mainAccountNeedPay,$secondAccountNeedPay);

        Db::startTrans();
        try {
            //修改订单状态
            OrderModel::where('order_id', '=', $this->orderID)->update(['order_status'=>2,'pay_time'=>2,'mainPay'=>$mainAccountNeedPay,'secondPay'=>$secondAccountNeedPay]);
            //减库存
            foreach ($status['pStatusArray'] as $singlePStatus) {
                if ($singlePStatus['option_id'] > 0) {
                    //商品有多选项，减对应选项的商品库存
                    ProductOption::where('goods_id', '=', $singlePStatus['goods_id'])->setDec('stock', $singlePStatus['quantity']);
                }else{
                    //商品无选项，直接减
                    ProductModel::where('goods_id', '=', $singlePStatus['goods_id'])->setDec('stock', $singlePStatus['quantity']);
                }
            }
            //减账户金额
            UserModel::where(['uid'=>$uid])->setDec('mainAccount',$mainAccountNeedPay);
            UserModel::where(['uid'=>$uid])->setDec('secondAccount',$secondAccountNeedPay);

            Db::commit();
            return ['errorCode' => 0,'msg'=>'支付成功'];
        } catch (Exception $e) {
            Db::rollback();
            throw $ex;
        }
    }
    /**
     * 判断用户主账户和小金库的余额是否充足
     */
    private function getUserAccountStatus($user,$mainAccountNeedPay,$secondAccountNeedPay){
        $mainAccountStatus = $user['mainAccount'] - $mainAccountNeedPay;
        $secondAccountStatus = $user['secondAccount'] - $secondAccountNeedPay;
        if ($mainAccountStatus < 0) {
            throw new OrderException([
                'msg' => '主账户余额不足,需最低充值'.abs($mainAccountStatus).'元',
                'errorCode' => 60002,
                'code' => 403
            ]);
        }elseif ($secondAccountStatus < 0) {
            throw new OrderException([
                'msg' => '小金库余额不足,需最低充值'.abs($secondAccountStatus).'元',
                'errorCode' => 60003,
                'code' => 403
            ]);
        }
    }
}