<?php

namespace app\api\model;

use think\Model;

class User extends BaseModel
{
    protected $table = 'osc_member';
    protected $autoWriteTimestamp = true;
    protected $createTime = 'regdate';

    public function orders()
    {
        return $this->hasMany('Order', 'user_id', 'id');
    }

    public function address()
    {
        return $this->hasOne('UserAddress', 'user_id', 'id');
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
