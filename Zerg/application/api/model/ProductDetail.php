<?php
/**
 * 商品详情图片模型
 */

namespace app\api\model;
use think\Model;

class ProductDetail extends BaseModel{

    protected $table = 'osc_goods_mobile_description_image';
    protected $hidden= ['mdi_id','goods_id','sort_order'];

}