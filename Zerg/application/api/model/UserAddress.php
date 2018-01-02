<?php

namespace app\api\model;

use think\Model;

class UserAddress extends BaseModel
{
    protected $table = 'osc_address';
    protected $autoWriteTimestamp = true;
    protected $hidden = ['create_time','update_time'];

    public static function getProvinceId($province){
        $provinces = Area::all(['area_parent_id'=>0]);
        foreach ($provinces as $key => $v) {
            if (strpos($province, $v['area_name']) !== false) {
                return $v['area_id'];
                break;
            }
        }

    }
}


