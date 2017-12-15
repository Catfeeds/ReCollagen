<?php
/**
 * 产品参数模型
 */

namespace app\api\model;
use think\Model;

class ProductProperty extends BaseModel{

    protected $table = 'osc_goods_param';
    protected $hidden= ['id','goods_id'];
}