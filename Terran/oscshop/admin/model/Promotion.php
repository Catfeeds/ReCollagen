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

    /**
     * 获取赠送商品
     */
    public function getFreeGoods($expression){
        $expression = json_decode($expression,true);
        $goods = [];
        foreach ($expression as $key => $v) {
            if ($v['goods_option_id']) {
                $goods[$key] = Db::name('goods')->alias('g')->field('g.goods_id,g.name,g.image,g.price,o.goods_option_id,o.option_name,o.option_price')
                    ->join('__GOODS_OPTION__ o','o.goods_id = g.goods_id','left')
                    ->where(['g.goods_id'=>$v['goods_id'],'o.goods_option_id'=>$v['goods_option_id']])
                    ->find();
            }else{
                $goods[$key] = Db::name('goods')->field('goods_id,name,image,price')
                    ->where(['goods_id'=>$v['goods_id']])
                    ->find();
                $goods[$key]['goods_option_id'] = $v['goods_option_id'];
                $goods[$key]['option_price'] = 0;
                $goods[$key]['option_name'] = '';
            }
        }

        return $goods;
    }
}
?>