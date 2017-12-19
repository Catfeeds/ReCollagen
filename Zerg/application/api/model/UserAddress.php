<?php

namespace app\api\model;

use think\Model;

class UserAddress extends BaseModel
{
    protected $table = 'osc_address';

    protected $hidden =['address_id', 'update_time'];

}


