<?php

namespace app\api\model;

use think\Model;

class Banner extends BaseModel
{
    /**
     * 修改图片路径
     */
    public function getImageAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }
    // public function items()
    // {
    //     return $this->hasMany('BannerItem', 'banner_id', 'id');
    // }
    //

    /**
     * @return Banner
     */
    public static function getBanners(){
        $banner = self::order('sort')->select();

//         $banner = BannerModel::relation('items,items.img')
//             ->find($id);
        return $banner;
    }
}
