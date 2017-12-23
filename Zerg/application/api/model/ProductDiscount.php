<?php
/**
 * 商品折扣模型
 */

namespace app\api\model;
use think\Model;

class ProductDiscount extends BaseModel{

    protected $table = 'osc_goods_discount';
    protected $hidden= ['product_discount_id','goods_id'];

}