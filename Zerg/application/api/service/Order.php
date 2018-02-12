<?php

namespace app\api\service;

use app\api\model\Cart;
use app\api\model\FinanceRecord;
use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\Order as OrderModel;
use app\api\model\UserAddress;
use app\api\model\ProductOption;
use app\api\model\Promotion;
use app\api\model\Dispatch;
use app\api\model\Transport;
use app\api\model\User as UserModel;

use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Db;
use think\Exception;
use think\Validate;
/**
 * 订单类
 * 订单做了以下简化：
 * 创建订单时会检测库存量，但并不会预扣除库存量，因为这需要队列支持
 * 未支付的订单再次支付时可能会出现库存不足的情况
 * 所以，项目采用3次检测
 * 1. 创建订单时检测库存
 * 2. 支付前检测库存
 * 3. 支付成功后检测库存
 */
class Order
{
    protected $postData;
    protected $oProducts;
    protected $products;
    protected $uid;

    function __construct()
    {
    }

    /**
     * @param int $uid 用户id
     * @param array $oProducts 订单商品列表
     * @return array 订单商品状态
     * @throws Exception
     */
    public function place($uid, $oProducts,$postData){
        $this->postData  = $postData;
        $this->oProducts = $oProducts;
        $this->products  = $this->getProductsByOrder($oProducts);
        $this->uid       = $uid;
        //用户状态检测
        $this->getUserStatus();
        //库存检测
        $status = $this->getOrderStatus();
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }

        $orderSnap      = $this->snapOrder();
        $status         = self::createOrderByTrans($orderSnap);
        $status['pass'] = true;
        return $status;
    }
    /**
     * 用户状态检测
     */
    private function getUserStatus(){
        $user = UserModel::get($this->uid);
        if ($user['checked'] != 1) {
            throw new UserException([
                'msg' => '账号未通过审核'
                ]);
        }

    }
    /**
     * 库存检测
     */
    private function getOrderStatus()
    {
        $status = [
            'pass'         => true,
            // 'orderPrice'   => 0,
            'pStatusArray' => []
        ];

        foreach ($this->oProducts as $oProduct) {            
            $pStatus = $this->getProductStatus($oProduct['goods_id'], $oProduct['quantity'], $this->products);
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            // $status['orderPrice'] += $pStatus['totalPrice'];
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }

    private function getProductStatus($oPID, $oCount, $products)
    {

        $pIndex = -1;
        $pStatus = [
            'goods_id'   => 0,
            'option_id'   => 0,
            'haveStock'  => false,
            'quantity'   => 0,
            'name'       => '',
            'totalPrice' => 0
        ];

        for ($i = 0; $i < count($products); $i++) {
            if ($oPID == $products[$i]['goods_id']) {
                $pIndex = $i;
            }
        }

        if ($pIndex == -1) {
            // 客户端传递的productid有可能根本不存在
            throw new OrderException(
                [
                    'msg' => 'id为' . $oPID . '的商品不存在，订单创建失败'
                ]);
        } else {
            $product = $products[$pIndex];
            $pStatus['goods_id']   = $product['goods_id'];
            $pStatus['option_id']  = isset($product['option_id']) ? $product['option_id'] : 0;
            $pStatus['name']       = $product['name'];
            $pStatus['quantity']   = $oCount;
            $pStatus['totalPrice'] = $product['price'] * $oCount;

            if ($product['stock'] - $oCount >= 0) {
                $pStatus['haveStock'] = true;
            }
        }

        return $pStatus;
    }
    /**
     * 根据订单查找真实商品
     */
    private function getProductsByOrder($oProducts)
    {
        $oPIDs = $optionIds = [];
        foreach ($oProducts as $item) {
            array_push($oPIDs, $item['goods_id']);
            $item['option_id']>0 && array_push($optionIds, $item['option_id']);
        }

        // 为了避免循环查询数据库
        $products = Product::all($oPIDs)
            ->visible(['goods_id', 'isMainGoods', 'price', 'stock', 'name', 'image'])
            ->toArray();
        if (!empty($optionIds)) {
            $options = ProductOption::all($optionIds)->toArray();

            foreach ($products as $k => $v) {
                foreach ($options as $k2 => $v2) {
                    if ($v['goods_id'] == $v2['goods_id']) {
                        $products[$k]['price']       = $v2['option_price'];
                        $products[$k]['stock']       = $v2['stock'];
                        $products[$k]['option_id']   = $v2['goods_option_id'];
                        $products[$k]['option_name'] = $v2['option_name'];
                    }
                }
            }
        }

        return $products;
    }

    // 创建订单时没有预扣除库存量，简化处理
    // 如果预扣除了库存量需要队列支持，且需要使用锁机制
    private function createOrderByTrans($snap){        
        Db::startTrans();
        try {
            //创建订单
            $order   = new OrderModel();
            $orderNo = $this->makeOrderNo();                //生成订单号
            $order->order_num_alias = $orderNo;     
            $order->pay_subject_img = $snap['snapImg'];     //快照商品图片
            $order->pay_subject     = $snap['snapName'];    //快照商品名称
            $order->uid             = $this->uid;
            //收货人信息
            $userAddress = $this->getUserAddress($this->postData['address_id']);
            $order->shipping_name   = $userAddress['name'];
            $order->shipping_tel    = $userAddress['telephone'];
            $order->shipping_addr   = $userAddress['province'].$userAddress['city'].$userAddress['country'].$userAddress['address'];
            $order->userRemarks     = $this->postData['userRemarks'];

            $order->dispatch_id     = $this->postData['dispatchId'];            //发货仓id
            $order->shipping_method = Db::name('transport')->getFieldById($this->postData['transId'],'title'); //物流公司
            $order->mainGoodsPrice  = $this->postData['mainGoodsPrice'];        //主商品价格
            $order->otherGoodsPrice = $this->postData['otherGoodsPrice'];       //辅销品价格
            $order->shippingPrice   = $this->postData['shippingPrice'];         //运费
            $order->total           = $order->mainGoodsPrice + $order->otherGoodsPrice + $order->shippingPrice;     //总计
            //促销活动
            if (!empty($this->postData['promotion'])) {
                $order->promotion = json_encode($this->postData['promotion']);
            }

            $order->save();
            
            //保存订单商品
            $orderID     = $order->order_id;
            $create_time = $order->create_time;
            foreach ($snap['pStatus'] as &$p) {
                $p['order_id'] = $orderID;
            }
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($snap['pStatus']);

            //删除购物车商品
            $cartModel = new Cart();
            foreach ($snap['pStatus'] as $v) {
                $cartModel->where(['uid'=>$this->uid,'goods_id'=>$v['goods_id'],'goods_option_id'=>$v['option_id']])->delete();
            }

            Db::commit();
            return [
                'order_no'    => $orderNo,
                'order_id'    => $orderID,
                'create_time' => $create_time
            ];
        } catch (Exception $ex) {
            Db::rollback();
            throw $ex;
        }
    }

    /**
      * 预检测并生成订单快照
      */ 
    private function snapOrder(){
        // status可以单独定义一个类
        $snap = [
            'mainGoodsPrice'  => 0,
            'otherGoodsPrice' => 0,
            'snapName'    => $this->products[0]['name'],
            'snapImg'     => $this->products[0]['image'],
            'pStatus'     => [],
        ];

        if (count($this->products) > 1) {
            $snap['snapName'] .= '等';
        }

        for ($i = 0; $i < count($this->products); $i++) {
            $product  = $this->products[$i];
            $oProduct = $this->oProducts[$i];
            
            $pStatus  = $this->snapProduct($product, $oProduct['quantity']);

            array_push($snap['pStatus'], $pStatus);
        }
        return $snap;
    }

    /**
      * 单个商品库存检测
      */ 
    private function snapProduct($product, $oCount){
        $pStatus = [
            'goods_id'     => null,
            'isMainGoods'  => null,
            'image'        => null,
            'name'         => null,
            'option_id'    => 0,
            'option_name'  => null,
            'quantity'     => $oCount,
            'price'        => 0,
            'total'        => 0,
        ];

        $pStatus['goods_id']     = $product['goods_id'];
        $pStatus['isMainGoods']  = $product['isMainGoods'];
        $pStatus['image']        = $product['image'];
        $pStatus['name']         = $product['name'];
        $pStatus['option_id']    = isset($product['option_id']) ? $product['option_id'] : 0;
        $pStatus['option_name']  = isset($product['option_name']) ? $product['option_name'] :'';
        $pStatus['quantity']     = $oCount;
        $pStatus['price']        = $product['price'];
        $pStatus['total']        = $oCount * $product['price'];
        return $pStatus;
    }
    /**
     * 获取用户的收货地址
     */
    private function getUserAddress($address_id){
        $userAddress = UserAddress::get($address_id);
        if (!$userAddress) {
            throw new UserException(
                [
                    'msg' => '用户收货地址不存在',
                    'errorCode' => 60001,
                ]);
        }
        return $userAddress->toArray();
    }

    /**
     * @param $address_id
     * @param $weight
     * @return string
     * @throws UserException
     * @throws \think\exception\DbException
     * Author: sai
     * DateTime: 2018/2/1 19:32
     * 根据用户地址和发货重量匹配发货仓id
     */
    private function getDispatchIdByAddr($address_id,$weight){
        $userAddress = $this->getUserAddress($address_id);
        $dispatchs = Dispatch::all();

        $dispath_id = '';
        foreach ($dispatchs as $key => $v) {
            if (strpos($v['area_id'],",".$userAddress['city_id'].",") !== false){
                if ($weight >= $v['min_weight'] && $weight <= $v['max_weight'] ) {
                    $dispath_id = $v['id'];
                }
            }
        }
        if ($dispath_id == '') {
            throw new UserException(
                [
                    'msg' => '该地址不在配送区域',
                    'errorCode' => 90002,
                ]);
        }

        return $dispath_id;
    }
    /**
     * 生成订单号
     */
    private static function makeOrderNo(){
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }
    /**
     * 检测订单库存
     */
    public function checkOrderStock($orderID)
    {
        //        if (!$orderNo)
        //        {
        //            throw new Exception('没有找到订单号');
        //        }

        // 一定要从订单商品表中直接查询
        // 不能从商品表中查询订单商品
        // 这将导致被删除的商品无法查询出订单商品来
        $oProducts = OrderProduct::where('order_id', '=', $orderID)
            ->select();            
        $this->products = $this->getProductsByOrder($oProducts);        
        $this->oProducts = $oProducts;
        $status = $this->getOrderStatus();

        return $status;
    }
    /**
     * 发货
     */
    public function delivery($orderID, $jumpPage = ''){
        $order = OrderModel::where('id', '=', $orderID)
            ->find();
        if (!$order) {
            throw new OrderException();
        }
        if ($order->status != OrderStatusEnum::PAID) {
            throw new OrderException([
                'msg'       => '订单未支付',
                'errorCode' => 80002,
                'code'      => 403
            ]);
        }
        $order->status = OrderStatusEnum::DELIVERED;
        $order->save();
//            ->update(['status' => OrderStatusEnum::DELIVERED]);
        $message = new DeliveryMessage();
        return $message->sendDeliveryMessage($order, $jumpPage);
    }
    /**
     * 取消订单
     */
    public function cancel($orderID,$uid){
        $order = OrderModel::where(['order_id'=>$orderID,'uid'=>$uid])
            ->find();
        if (!$order) {
            throw new OrderException();
        }
        //待付款和待发货需要可以取消
        if ($order->order_status != 1 && $order->order_status != 2) {
            throw new OrderException([
                'msg'       => '订单状态异常',
                'errorCode' => 80003,
                'code'      => 400
            ]);
        }
        Db::startTrans();
        try {
            //如果是待发货订单
            if ($order->order_status == 2) {
                //订单主账户消费金额增加到用户主账户
                UserModel::where(['uid'=>$uid])->setInc('mainAccount',$order['mainPay']);
                $user = UserModel::get($uid);
                $recordModel = new FinanceRecord();
                $recordModel->insert(['uid' => $uid,'amount' => $order['mainPay'],'balance' => $user['mainAccount'],'addtime' => time(),'reason' => '取消订单，用户主账户金额退回（订单号：'.$order['order_num_alias'].'）','rectype' => 1]);
                //订单小金库消费金额增加到用户小金库
                UserModel::where(['uid'=>$uid])->setInc('secondAccount',$order['secondPay']);
                $recordModel->insert(['uid' => $uid,'amount' => $order['secondPay'],'balance' => $user['secondAccount'],'addtime' => time(),'reason' => '取消订单，用户小金库金额退回（订单号：'.$order['order_num_alias'].'）','rectype' => 2]);

                //如果已经返现的话，退回返现金额
                $promotion = json_decode($order['promotion']);
                if ($promotion) {
                    foreach ($promotion as $v) {
                        if ($v->type == 2) {
                            UserModel::where(['uid'=>$uid])->setDec('mainAccount',$v->free);
                            //写入财务流水
                            $user = UserModel::get($uid);
                            $recordModel->insert(['uid' => $uid,'amount' => '-'.$v->free,'balance' => $user['mainAccount'],'addtime' => time(),'reason' => '取消订单，返现退回系统（订单号：'.$order['order_num_alias'].'）','rectype' => 1]);
                        }
                    }
                }
            }

            //修改订单状态
            $order->order_status = 5;
            $order->save();

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

    }
    /**
     * 删除订单
     */
    public function del($orderID,$uid){
        $order = OrderModel::where(['order_id'=>$orderID,'uid'=>$uid])
            ->find();
        if (!$order) {
            throw new OrderException();
        }
        return $order->delete();
    }
    /**
     * 确认收货
     */
    public function receive($orderID,$uid){
        $order = OrderModel::where(['order_id'=>$orderID,'uid'=>$uid])
            ->find();
        if (!$order) {
            throw new OrderException();
        }
        if ($order->order_status != 3) {
            throw new OrderException([
                'msg'       => '订单状态异常',
                'errorCode' => 80003,
                'code'      => 400
            ]);
        }
        Db::startTrans();
        try {
            //修改订单状态
            $order->order_status = 4;
            $order->receive_time = date('Y-m-d H:i:s');
            $order->save();
//            //如果符合返现，返现金额增加到用户账户
//            $promotion = json_decode($order['promotion']);
//            if ($promotion) {
//                foreach ($promotion as $v) {
//                    if ($v->type == 2) {
//                        UserModel::where(['uid'=>$uid])->setInc('mainAccount',$v->free);
//                        //写入财务流水
//                        $user = UserModel::get($uid);
//                        $recordModel = new FinanceRecord();
//                        $recordModel->insert(['uid' => $uid,'amount' => $v->free,'balance' => $user['mainAccount'],'addtime' => time(),'reason' => '订单返现（订单号：'.$order['order_num_alias'].'）','rectype' => 1]);
//                    }
//                }
//            }

            Db::commit();
            return true;
        } catch (Exception $e) {
            Db::rollback();
            throw $e;
        }

    }
    /**
     * 根据商品重量匹配物流公司和运费
     */
    public function getTransFee($data){

        $rule = [
            ['weight','require|float','商品重量不能为空|商品重量必须是数字'],
            ['address_id','require|number','地址id不能为空|地址id必须是数字']
        ];
        $validate = new Validate($rule);
        $result   = $validate->check($data);
        if(!$result){
            throw new OrderException([
                'msg'       => $validate->getError(),
                'errorCode' => 90001,
                'code'      => 400
                ]);
        }
        $this->uid   = Token::getCurrentUid();
//        $this->uid   = 2;
        if ($data['address_id']) {
            $userAddress = UserAddress::get($data['address_id']);
        }else{
            $userAddress = UserAddress::where(['uid'=>$this->uid,'is_default'=>1])->find();
        }
        if (!$userAddress) {
            $trans['fee'] = 0;
            $trans['transId'] = 0;
            return $trans;
        }

        //匹配设置了配送区域的物流公司
        $transports = Transport::all(['is_default'=>2]);
        $supportTrans = []; //支持配送到用户地址的物流公司
        foreach ($transports as $key => $v) {
            if (strpos($v['area_id'],",".$userAddress['city_id'].",") !== false){
                $supportTrans[] = $v; 
            }
        }

        //计算匹配到的物流公司需要的运费和对应的物流公司id
        $feeArr = $transIdArr = $transTitleArr = [];
        $fee = $transId = 0;

        if (!empty($supportTrans)) {
            foreach ($supportTrans as $key => $v) {
                $transIdArr[] = $v['transport_id'];
                $transTitleArr[] = $v['transport_title'];

                if ($data['weight'] <= $v['snum']){
                    //在首重数量范围内
                    $feeArr[] = $v['sprice'];
                }else{
                    //超出首重数量范围，需要计算续重
                    $feeArr[] = sprintf('%.2f',($v['sprice'] + ceil(($data['weight']-$v['snum'])/$v['xnum'])*$v['xprice']));
                }
            }
            $fee     = min($feeArr);
            $transId = $transIdArr[array_keys($feeArr,min($feeArr))[0]];
            $transTitle = $transTitleArr[array_keys($feeArr,min($feeArr))[0]];
        }else{
            //如果没有设置配送到用户地址的物流公司，选择默认的全国运费
            $transports2 = Transport::all(['is_default'=>1])->toArray();
            foreach ($transports2 as $key => $v) {
                $transIdArr[] = $v['transport_id'];
                $transTitleArr[] = $v['transport_title'];

                if ($data['weight'] <= $v['snum']){
                    //在首重数量范围内
                    $feeArr[] = $v['sprice'];
                }else{
                    //超出首重数量范围，需要计算续重
                    $feeArr[] = sprintf('%.2f',($v['sprice'] + ceil(($data['weight']-$v['snum'])/$v['xnum'])*$v['xprice']));
                }
            }
            $fee     = min($feeArr);
            $transId = $transIdArr[array_keys($feeArr,min($feeArr))[0]];
            $transTitle = $transTitleArr[array_keys($feeArr,min($feeArr))[0]];
        }

        $trans['fee'] = $fee;
        $trans['transId'] = $transId;
        $trans['transTitle'] = $transTitle;
        //计算发货仓
        $trans['dispatchId'] = $this->getDispatchIdByAddr($data['address_id'],$data['weight']);
        $trans['dispatchTitle'] = '';
        if ($trans['dispatchId']) {
            $dispatch = Dispatch::get($trans['dispatchId']);
            $trans['dispatchTitle'] = $dispatch['dispatch_title'];
        }

        return $trans;

    }
}