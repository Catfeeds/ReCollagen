<?php

namespace app\api\model;

use think\Model;

class UserAddress extends BaseModel
{
    protected $table = 'osc_address';
    protected $autoWriteTimestamp = true;
    protected $hidden = ['create_time','update_time'];

    /**
     * 获取省份id
     * @param $province
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function getProvinceId($province){
        $provinces = Area::all(['area_parent_id'=>0]);
        foreach ($provinces as $key => $v) {
            if (strpos($province, $v['area_name']) !== false) {
                return $v['area_id'];
                break;
            }
        }
    }
    /**
     * 获取城市id
     * @param $city
     * @return mixed
     * @throws \think\exception\DbException
     */
    public static function getCityId($city){
        $citys = Area::all(['area_deep'=>2]);
        foreach ($citys as $key => $v) {
            if (strpos($city, $v['area_name']) !== false) {
                return $v['area_id'];
                break;
            }
        }
    }
}


