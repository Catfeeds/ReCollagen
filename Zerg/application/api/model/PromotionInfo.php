<?php

namespace app\api\model;

use think\Model;

class PromotionInfo extends BaseModel
{
    protected $hidden = ['id','update_time'];

    /**
     * 修改图片路径
     */
    public function getImageAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }
}
