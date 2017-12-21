<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\model\Promotion AS PromotionModel;

/**
 * 促销活动
 */
class Promotion extends Controller
{

    /**
     * 获取正在进行中的促销活动
     */
    public function getPromotions(){

        $promotions = PromotionModel::all(['start_time'=>['lt',time()],'end_time'=>['gt',time()]])->toArray();

        return $promotions;
    }

}