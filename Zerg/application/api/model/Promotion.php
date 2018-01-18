<?php

namespace app\api\model;

use think\Model;

class Promotion extends BaseModel
{
    protected $hidden = ['start_time','end_time'];

    public function products()
    {
        return $this->hasMany('PromotionGoods', 'promotion_id', 'id');
    }

    /**
     * 获取正在促销的活动
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public static function getPromotions()
    {
        $promotions = self::all(['start_time'=>['lt',time()],'end_time'=>['gt',time()]]);
//        if (!empty($promotions)) {
//            foreach ($promotions as $key => $promotion) {
//                $promotionGoods = PromotionGoods::products($promotion['id'])->toArray();
//                $promotions[$key]['goods'] = $promotionGoods;
//            }
//        }
        return $promotions;
    }

    /**
     * 返回预订单最优促销
     * @param $data
     * @return array|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getPreOrderPromotions($data)
    {
        $promotions = [];
        foreach ($data as $key => $v) {
            $promotions[] = self::where(['id'=>$key,'start_time'=>['lt',time()],'end_time'=>['gt',time()],'money'=>['elt',$v]])->find();

        }
        $promotion = [];
        if ($promotions) {
            foreach ($promotions as $key => $v) {
                if (empty($promotion)) {
                    $promotion = $v;
                }else{
                    if ($v['money'] > $promotion['money']) {
                        $promotion = $v;
                    }
                }
            }
        }
        if ($promotion) {
            if ($promotion['type'] == 1) {              //满额打折活动获取优惠金额
                //活动对应的商品总价
                $totalPrice = $data[$promotion['id']];
                $promotion['free'] = $totalPrice - $totalPrice*$promotion['expression']/100;
            }elseif ($promotion['type'] == 2){          //满额返现活动获取返现金额
                $totalPrice = $data[$promotion['id']];
                //每满最低金额多返一次
                $promotion['free'] = floor($totalPrice/$promotion['money']) * $promotion['expression'];
            }elseif ($promotion['type'] == 3){          //满额返现活动获取赠送商品
                $goodsIds = explode(',',$promotion['expression']);
                //赠送商品
                $promotion['free'] = Product::getcollectGoodsList($goodsIds);
                //赠送商品件数(每满最低金额多送一件)
                $totalPrice = $data[$promotion['id']];
                $promotion['freeCount'] = floor($totalPrice/$promotion['money']);
            }
        }

        return $promotion;
    }
}
