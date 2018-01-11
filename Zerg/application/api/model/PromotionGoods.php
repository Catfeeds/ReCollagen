<?php

namespace app\api\model;

use think\Model;
use think\Db;

class PromotionGoods extends BaseModel
{
    protected $hidden = ['id','promotion_id'];

//    public function goods()
//    {
//        return $this->hasOne('Product', 'goods_id', 'goods_id');
//    }
//    public function option()
//    {
//        return $this->hasOne('ProductOption', 'goods_option_id', 'goods_option_id');
//    }

    public static function products($promotion_id)
    {

        $products = Db::name('promotion_goods')->alias('p')->field('p.goods_id,p.goods_option_id,g.image,g.name,g.price,g.stock,g.weight,g.status,o.option_name,o.option_price')
            ->join('__GOODS__ g','g.goods_id=p.goods_id','left')
            ->join('__GOODS_OPTION__ o','o.goods_option_id=p.goods_option_id','left')
            ->where(['p.promotion_id'=>$promotion_id])
            ->select();

        return $products;
    }
}
