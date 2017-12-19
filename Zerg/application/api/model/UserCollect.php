<?php
/**
 * 用户商品收藏模型
 */
namespace app\api\model;

use think\Model;
use app\api\service\Token;


class UserCollect extends BaseModel{
    
    protected $table = 'osc_member_collect';

    /**
     * 收藏或取消收藏商品
     */
    public static function collectGoods($id,$type){

        $currentUid = Token::getCurrentUid();
        halt($currentUid);

        if ($type == 'add') {
            self::save(['uid'=>$currentUid,'goods_id'=>$id]);
        }elseif ($type == 'cancel') {
            self::destroy(['uid'=>$currentUid,'goods_id'=>$id]);
        }

    }

}


