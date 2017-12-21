<?php

namespace app\api\model;

use think\Model;

class Promotion extends BaseModel
{
    protected $hidden = ['id','start_time','end_time'];

}
