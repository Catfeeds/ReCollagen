<?php

namespace app\api\model;

use think\Model;

class Order extends BaseModel
{
    protected $hidden = ['uid', 'update_time', 'dispatch_id', 'pay_time'];
    protected $autoWriteTimestamp = true;

    // public function getSnapItemsAttr($value)
    // {
    //     if(empty($value)){
    //         return null;
    //     }
    //     return json_decode($value);
    // }

    // public function getSnapAddressAttr($value){
    //     if(empty($value)){
    //         return null;
    //     }
    //     return json_decode(($value));
    // }
    /**
     * 根据用户id获取订单列表（简要信息）
     */
    public static function getSummaryByUser($uid, $status, $page)
    {
        $data = self::with('products')->where(['uid' => $uid, 'order_status' => $status])
            ->order('create_time desc')
            ->paginate(10, true, ['page' => $page]);
        if (!empty($data)) {
            foreach ($data as $key => $v) {
                $data[$key]['productCount'] = count($v['products']);
            }
        }
        return $data;
    }

    public static function getSummaryByPage($page = 1, $size = 20)
    {
        $pagingData = self::order('create_time desc')
            ->paginate($size, true, ['page' => $page]);
        return $pagingData;
    }

    public function products()
    {
        return $this->hasMany('OrderProduct', 'order_id', 'order_id');
    }

    /**
     * 根据订单id获取订单详情
     */
    public static function getDetail($id)
    {

        $orderDetail = self::with('products')->find($id);
        foreach ($orderDetail['products'] as $key => $product ) {
            $orderDetail['products'][$key]['counts']       = $product['quantity'];
            $orderDetail['products'][$key]['currentPrice'] = $product['price'];
            unset($orderDetail['products'][$key]['quantity']);
            unset($orderDetail['products'][$key]['price']);
        }
//        halt($orderDetail['products']->toArray());
        if (!empty($orderDetail['promotion'])) {
            $promotion                    = json_decode($orderDetail['promotion'], true);
            $orderDetail['promotionName'] = array_column($promotion, 'name');
        }
        return $orderDetail;
    }

}
