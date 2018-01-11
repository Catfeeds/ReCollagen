<?php
namespace osc\admin\model;
use think\Db;
use think\Model;

class Promotion extends Model{
	
	/**
     * 根据促销id获取所属促销商品
     */
	public function getPromotionGoods($id){
	    $goods = Db::name('promotion_goods')->alias('p')->field('p.*,g.name,g.image,g.price,o.option_name,o.option_price')
            ->join('__GOODS__ g','g.goods_id = p.goods_id')
            ->join('__GOODS_OPTION__ o','o.goods_option_id = p.goods_option_id','left')
            ->where(['p.promotion_id'=>$id])
            ->select();

	    return $goods;
    }
}
?>