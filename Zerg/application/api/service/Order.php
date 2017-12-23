<?php

namespace app\api\service;

use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\Order as OrderModel;
use app\api\model\UserAddress;
use app\api\model\ProductOption;

use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use think\Db;
use think\Exception;

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
     * 库存检测
     */
    private function getOrderStatus()
    {
        $status = [
            'pass'         => true,
            'orderPrice'   => 0,
            'pStatusArray' => []
        ];

        foreach ($this->oProducts as $oProduct) {
            $pStatus =
                $this->getProductStatus(
                    $oProduct['goods_id'], $oProduct['count'], $this->products);
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }

    private function getProductStatus($oPID, $oCount, $products)
    {
        $pIndex = -1;
        $pStatus = [
            'goods_id'         => null,
            'haveStock'  => false,
            'count'      => 0,
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
            $pStatus['name']       = $product['name'];
            $pStatus['count']      = $oCount;
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
            $item['option_id'] && array_push($optionIds, $item['option_id']);
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

        try {
            //创建订单
            $order   = new OrderModel();
            $orderNo = $this->makeOrderNo();                //生成订单号
            $order->order_num_alias = $orderNo;     
            $order->pay_subject_img = $snap['snapImg'];     //快照商品图片
            $order->pay_subject     = $snap['snapName'];    //快照商品名称
            $order->uid             = $this->uid;
            //收货人信息
            $userAddress = $this->getUserAddress();
            $order->shipping_name   = $userAddress['name'];
            $order->shipping_tel    = $userAddress['telephone'];
            $order->shipping_addr   = $userAddress['province'].$userAddress['city'].$userAddress['country'].$userAddress['address'];

            $order->dispatch_id     = '111';     //发货仓id
            $order->shipping_method = '物流公司';     //物流公司
            $order->mainGoodsPrice  = $this->postData['mainGoodsPrice'];     //主商品价格
            $order->otherGoodsPrice = $this->postData['otherGoodsPrice'];     //辅销品价格
            $order->shippingPrice   = $this->postData['shippingPrice'];  //运费
            $order->total           = $order->mainGoodsPrice + $order->otherGoodsPrice + $order->shippingPrice;     //总计

            $order->save();
            
            //保存订单商品
            $orderID     = $order->order_id;
            $create_time = $order->create_time;
            foreach ($snap['pStatus'] as &$p) {
                $p['order_id'] = $orderID;
            }

            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($snap['pStatus']);

            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time
            ];
        } catch (Exception $ex) {
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
            $product = $this->products[$i];
            $oProduct = $this->oProducts[$i];

            $pStatus = $this->snapProduct($product, $oProduct['count']);

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
            'option_name'  => null,
            'quantity'     => $oCount,
            'price'        => 0,
            'total'        => 0,
        ];

        $pStatus['goods_id']     = $product['goods_id'];
        $pStatus['isMainGoods']  = $product['isMainGoods'];
        $pStatus['image']        = $product['image'];
        $pStatus['name']         = $product['name'];
        $pStatus['option_name']  = isset($product['option_name']) ? $product['option_name'] :'';
        $pStatus['quantity']     = $oCount;
        $pStatus['price']        = $product['price'];
        $pStatus['total']        = $oCount * $product['price'];
        return $pStatus;
    }
    /**
     * 获取用户的收货地址
     */
    private function getUserAddress()
    {
        $userAddress = UserAddress::where('uid', '=', $this->uid)
            ->find();
        if (!$userAddress) {
            throw new UserException(
                [
                    'msg' => '用户收货地址不存在，下单失败',
                    'errorCode' => 60001,
                ]);
        }
        return $userAddress->toArray();
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
     * @param string $orderNo 订单号
     * @return array 订单商品状态
     * @throws Exception
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

    public function delivery($orderID, $jumpPage = '')
    {
        $order = OrderModel::where('id', '=', $orderID)
            ->find();
        if (!$order) {
            throw new OrderException();
        }
        if ($order->status != OrderStatusEnum::PAID) {
            throw new OrderException([
                'msg' => '还没付款呢，想干嘛？或者你已经更新过订单了，不要再刷了',
                'errorCode' => 80002,
                'code' => 403
            ]);
        }
        $order->status = OrderStatusEnum::DELIVERED;
        $order->save();
//            ->update(['status' => OrderStatusEnum::DELIVERED]);
        $message = new DeliveryMessage();
        return $message->sendDeliveryMessage($order, $jumpPage);
    }
}