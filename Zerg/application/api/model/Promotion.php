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

    public static function getPromotions()
    {
        $promotions = self::all(['start_time'=>['lt',time()],'end_time'=>['gt',time()]]);
        if (!empty($promotions)) {
            foreach ($promotions as $key => $promotion) {
                $promotionGoods = PromotionGoods::products($promotion['id'])->toArray();
                $promotions[$key]['goods'] = $promotionGoods;
            }
        }
        return $promotions;
    }

}
