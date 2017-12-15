<?php
/**
 * 商品详情页滚动图模型
 */

namespace app\api\model;
use think\Model;

class ProductImage extends BaseModel{

    protected $table = 'osc_goods_image';

    protected $hidden = ['goods_image_id', 'goods_id', 'sort_order'];
    // public function imgUrl()
    // {
    //     return $this->belongsTo('Image', 'img_id', 'id');
    // }

    /**
     * 修改图片路径
     */
    public function getImageAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }

}