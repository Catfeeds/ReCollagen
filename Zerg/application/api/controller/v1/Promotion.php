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

        $promotions = PromotionModel::getPromotions();

        return $promotions;
    }

    /**
     * 获取促销信息
     */
    public function getPromotionInfo(){
        $promotionInfo = PromotionInfoModel::get()->hidden(['image']);
        $promotionInfo['description'] = htmlspecialchars_decode($promotionInfo['description']);

        return $promotionInfo;
    }
    /**
     * 获取促销图片
     */
    public function getPromotionImg(){
        $promotionInfo = PromotionInfoModel::get()->hidden(['description']);

        //判断是否有正在进行中的促销活动，没有则不显示
        $promotions = $this->getPromotions();
        $promotionInfo['isValid'] = $promotions->isEmpty() ? -1 : 1;

        return $promotionInfo;
    }

}