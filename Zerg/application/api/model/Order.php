<?php

namespace app\api\model;

use think\Model;

class Order extends BaseModel
{
    protected $hidden = ['uid','update_time','dispatch_id','pay_time'];
    protected $autoWriteTimestamp = true;

    /**
     * 修改图片路径
     */
    public function getPaySubjectImgAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }

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
    public static function getSummaryByUser($uid){
        $data = self::with('products')->where('uid', '=', $uid)
            ->order('create_time desc')
            ->select();
        if (!empty($data)) {
            foreach ($data as $key => $v) {
                $data[$key]['productCount'] = count($v['products']);
            }
        }
        return $data;
    }

    public static function getSummaryByPage($page=1, $size=20){
        $pagingData = self::order('create_time desc')
            ->paginate($size, true, ['page' => $page]);
        return $pagingData ;
    }

    public function products()
    {
        return $this->hasMany('OrderProduct', 'order_id', 'order_id');
    }

    /**
     * 根据订单id获取订单详情
     */
    public static function getDetail($id){

        $orderDetail = self::with('products')->find($id);

        return $orderDetail;
    }

}
