<?php
/**
 * 商品选项模型
 */

namespace app\api\model;
use think\Model;

class ProductOption extends BaseModel{

    protected $table = 'osc_goods_option';
    protected $hidden= ['goods_id','sort','option_id'];

}