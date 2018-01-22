<?php

namespace app\api\model;

use think\Model;

class Cart extends BaseModel{

    protected $autoWriteTimestamp = 1;
    protected $hidden = ['create_time', 'update_time'];

    /**
     * 修改图片路径
     */
    public function getImageAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }
    /**
     * 获取购物车商品
     * @param $uid
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getCartGoodsByUid($uid){
        $goodsList = self::alias('c')->field('c.*,g.name,g.isMainGoods,g.image,g.price,g.stock,o.option_name,o.option_price,o.stock AS option_stock')
            ->join('__GOODS__ g','g.goods_id = c.goods_id','left')
            ->join('__GOODS_OPTION__ o','o.goods_option_id = c.goods_option_id','left')
            ->where(['c.uid'=>$uid])
            ->select();

        return $goodsList;
    }

    /**
     * 获取预下单详情清单
     * @param $uid
     * @param $checked  all:购物车商品；1代表选择商品，预下单
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getPreOrderDetailByUid($uid,$checked){
        $where = ['c.uid'=>$uid];
        if ($checked == 1) {
            $where['c.isChecked'] = $checked;
        }
        $data['goodsList'] = self::alias('c')
            ->field('c.goods_id,c.goods_option_id,c.count,c.isChecked,g.name,g.isMainGoods,g.image,g.price,g.stock,g.promotion1_id,g.promotion2_id,g.promotion3_id,g.promotion4_id,o.option_name,o.option_price,o.stock AS option_stock')
            ->join('__GOODS__ g','g.goods_id = c.goods_id','left')
            ->join('__GOODS_OPTION__ o','o.goods_option_id = c.goods_option_id','left')
            ->where($where)
            ->select();
        if ($data['goodsList']) {
            //统计单件商品总价格
            foreach ($data['goodsList'] as $key => $v) {
                if ($v['goods_option_id']) {
                    $data['goodsList'][$key]['price'] = $v['option_price'];
                    $data['goodsList'][$key]['stock'] = $v['option_stock'];
                    $data['goodsList'][$key]['totalPrice'] = $v['count'] * $v['option_price'];
                }else{
                    $data['goodsList'][$key]['totalPrice'] = $v['count'] * $v['price'];
                }
            }
            //统计单个商品第几件几折
            foreach ($data['goodsList'] as $key => $v) {
                $dis = ProductDiscount::where(['goods_id'=>$v['goods_id']])->order('quantity desc')->select();
                if ($dis) {
                    foreach ($dis as $key2 => $v2) {
                        if ($v['count'] >= $v2['quantity']) {
                            $price = $v['price']*$v2['discount']/100;
                            $data['goodsList'][$key]['price'] = $price;
                            $data['goodsList'][$key]['totalPrice'] = $v['count'] * $price;
                            break;
                        }
                    }
                }
            }

            //统计满额打折活动(促销一)
            $condition1 = self::getPreOrderPromotionsCondition($data['goodsList'],'promotion1_id');
            $data['promotion1'] = Promotion::getPreOrderPromotions($condition1);
            //统计满额返现活动(促销二)
            $condition2 = self::getPreOrderPromotionsCondition($data['goodsList'],'promotion2_id');
            $data['promotion2'] = Promotion::getPreOrderPromotions($condition2);
            //统计满额赠送商品活动(促销三)
            $condition3 = self::getPreOrderPromotionsCondition($data['goodsList'],'promotion3_id');
            $data['promotion3'] = Promotion::getPreOrderPromotions($condition3);
            //统计第X件商品X折(促销四)
            $condition4 = self::getPreOrderPromotionsCondition($data['goodsList'],'promotion4_id');
            $data['promotion4'] = Promotion::getPreOrderPromotions($condition4);
            if ($data['promotion4']) {
                foreach ($data['goodsList'] as $key => $v) {
                    if ($v['promotion4_id'] == $data['promotion4']['id']) {
                        $pro4Goods[] = $v;
                    }
                }
                //按单价降序排列
                sortArrByField($pro4Goods, 'price', $desc = true);
                $expression = explode(',',$data['promotion4']['expression']);
                $discount = $expression[0];
                $piece = (int)$expression[1];

                if (count($pro4Goods) >= $piece) {
                    //如果商品件数达到促销的件数要求
                    foreach ($data['goodsList'] as $key => $v) {
                        if ($v['goods_id'] == $pro4Goods[$piece-1]['goods_id'] && $v['goods_option_id'] == $pro4Goods[$piece-1]['goods_option_id']) {
                            $data['goodsList'][$key]['price'] = $v['price']*$discount/100;      //将满足第X件商品X折活动的该商品修改为活动价
                            $data['goodsList'][$key]['totalPrice'] = $v['price']*$discount/100 * $v['count'];
                            $data['promotion4']['free'] = ($v['price'] - $v['price']*$discount/100) * $v['count'];  //第X件商品X折活动优惠金额
                        }
                    }
                }else{
                    $data['promotion4']['free'] = 0;
                }

            }
        }

        return $data;
    }

    private static function getPreOrderPromotionsCondition($goodsList,$promotionId){
        $promotions = $condition = [];
        foreach ($goodsList as $key => $v) {
            if ($v[$promotionId]) {
                $promotions[$key][$promotionId] = $v[$promotionId];
                $promotions[$key]['totalPrice'] = $v['totalPrice'];
            }
        }
        if ($promotions) {
            foreach ($promotions as $key => $v) {
                if(isset($condition[$v[$promotionId]])){
                    $condition[$v[$promotionId]]=$condition[$v[$promotionId]]+$v['totalPrice'];
                }else{
                    $condition[$v[$promotionId]]=$v['totalPrice'];
                }
            }
        }
        return $condition;
    }

}
