<?php
/**
 * 用户商品收藏模型
 */
namespace app\api\model;

use think\Model;


class UserCollect extends BaseModel{
    
    protected $table = 'osc_member_collect';
    protected $autoWriteTimestamp = true;

}


