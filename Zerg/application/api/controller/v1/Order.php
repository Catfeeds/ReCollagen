<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Order as OrderModel;
use app\api\service\Order as OrderService;
use app\api\service\Token;

use app\api\validate\IDMustBePositiveInt;
use app\api\validate\OrderPlace;
use app\api\validate\PagingParameter;
use app\lib\exception\OrderException;
use app\lib\exception\SuccessMessage;
use think\Controller;

class Order extends BaseController
{
//    protected $beforeActionList = [
//        'checkExclusiveScope' => ['only' => 'createOrder,cancelOrder,receiveOrder'],
//        'checkPrimaryScope'   => ['only' => 'getDetail,getSummaryByUser'],
//        'checkSuperScope'     => ['only' => 'delivery,getSummary']
//    ];

    /**
     * 下单
     * @url /order
     * @HTTP POST
     */
    public function createOrder()
    {
        (new OrderPlace())->goCheck();
        $postData = input('post.');
        $products = input('post.goodsArrInfo/a');
        $uid      = Token::getCurrentUid();
//         $uid      = 2;

        $order  = new OrderService();
        $status = $order->place($uid, $products, $postData);

        return $status;
    }

    /**
     * 根据订单id获取订单详情
     * @param $id
     * @return static
     * @throws OrderException
     * @throws \app\lib\exception\ParameterException
     */
    public function getDetail($id)
    {

        (new IDMustBePositiveInt())->goCheck();
        $orderDetail = OrderModel::getDetail($id);
        if (!$orderDetail) {
            throw new OrderException();
        }
        $orderDetail = $orderDetail
            ->hidden(['pay_subject_img', 'pay_subject', 'mainPay', 'secondPay'])->toArray();

        return $orderDetail;
    }

    /**
     * 根据订单类型获取某个用户订单列表（简要信息）
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummaryByUser($status=1, $page = 1)
    {
        $uid = Token::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByUser($uid, $status, $page);
        if ($pagingOrders->isEmpty()) {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ];
        }

        $data = $pagingOrders->hidden(['shipping_tel', 'shipping_method', 'shipping_num', 'mainPay', 'secondPay', 'mainGoodsPrice', 'otherGoodsPrice', 'shippingPrice', 'promotion', 'create_time', 'products'])
            ->toArray();

        return [
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ];

    }

    /**
     * 获取全部订单简要信息（分页）
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummary($page = 1, $size = 20)
    {
        (new PagingParameter())->goCheck();
//        $uid = Token::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByPage($page, $size);
        if ($pagingOrders->isEmpty()) {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ];
        }
        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])
            ->toArray();
        return [
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ];
    }

    public function delivery($id)
    {
        (new IDMustBePositiveInt())->goCheck();
        $order   = new OrderService();
        $success = $order->delivery($id);
        if ($success) {
            return new SuccessMessage();
        }
    }

    /**
     * 根据订单id查询物流进度
     */
    public function getTransInfo($id)
    {

        $orderDetail  = $this->getDetail($id);
        $shippingCode = getShippingCode($orderDetail['shipping_method']);
        $url          = 'https://m.kuaidi100.com/query?type=' . $shippingCode . '&postid=' . $orderDetail['shipping_num'];
        $info         = curl_post($url);

        return json_decode($info);
    }

    /**
     * 取消订单
     */
    public function cancelOrder($id)
    {
        (new IDMustBePositiveInt())->goCheck();
        $uid = Token::getCurrentUid();

        $order   = new OrderService();
        $success = $order->cancel($id, $uid);
        if ($success) {
            return new SuccessMessage();
        }
    }

    /**
     * 确认收货
     */
    public function receiveOrder($id)
    {
        (new IDMustBePositiveInt())->goCheck();
        $uid = Token::getCurrentUid();
//        $uid = 2;

        $order   = new OrderService();
        $success = $order->receive($id, $uid);
        if ($success) {
            return new SuccessMessage();
        }
    }

    /**
     * 根据商品重量匹配物流公司和运费
     */
    public function getTransFee()
    {
        $order = new OrderService();
        return $order->getTransFee(input('post.'));
    }
}






















