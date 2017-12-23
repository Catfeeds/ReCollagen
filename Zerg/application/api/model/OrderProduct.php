<?php
/**
 * 订单商品模型
 */
namespace app\api\model;

use think\Model;

class OrderProduct extends BaseModel
{
    protected $autoWriteTimestamp = true;
    protected $table = 'osc_order_goods';
    protected $hidden = ['order_goods_id','order_id','create_time','update_time'];

    /**
     * 修改图片路径
     */
    public function getImageAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }

}
