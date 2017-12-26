<?php

namespace app\api\model;

use think\Model;

/**
 * 物流公司
 */
class Transport extends BaseModel
{
    protected $table = 'osc_transport_extend';
    protected $hidden = [
        'top_area_id','is_default'
    ];

}