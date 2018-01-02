<?php

namespace app\api\model;

use think\Model;

class User extends BaseModel
{
    protected $table = 'osc_member';
    protected $autoWriteTimestamp = true;

    public function orders()
    {
        return $this->hasMany('Order', 'uid', 'uid');
    }

    public function address()
    {
        return $this->hasMany('UserAddress', 'uid', 'uid');
    }

    /**
     * 用户是否存在
     * 存在返回uid，不存在返回0
     */
    public static function getByOpenID($openid)
    {
        $user = User::where('openId', '=', $openid)
            ->find();
        return $user;
    }
}
