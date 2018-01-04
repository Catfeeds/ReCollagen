<?php

namespace app\api\controller\v1;

use think\Controller;
use app\api\model\Promotion AS PromotionModel;
use app\api\model\PromotionInfo AS PromotionInfoModel;

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

    /**
     * 获取促销信息
     */
    public function getPromotionInfo(){

        $promotionInfo = PromotionInfoModel::get()->hidden(['image']);

        return $promotionInfo;
    }
    /**
     * 获取促销图片
     */
    public function getPromotionImg(){

        $promotionInfo = PromotionInfoModel::get()->hidden(['description']);

        return $promotionInfo;
    }

}