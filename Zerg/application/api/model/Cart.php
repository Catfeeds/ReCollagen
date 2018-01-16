<?php

namespace app\api\model;

use think\Model;

class Cart extends BaseModel{

    protected $autoWriteTimestamp = 1;
    protected $hidden = ['create_time', 'update_time'];

    /**
     * 修改图片路径
     */
    public function getImageAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }

    /**
     * 获取购物车商品
     * @param $uid
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getCartGoodsByUid($uid){
        $goodsList = self::alias('c')->field('c.*,g.name,g.isMainGoods,g.image,g.price,g.stock,o.option_name,o.option_price,o.stock AS option_stock')->where(['c.uid'=>$uid])
            ->join('__GOODS__ g','g.goods_id = c.goods_id','left')
            ->join('__GOODS_OPTION__ o','o.goods_option_id = c.goods_option_id','left')
            ->select();

        return $goodsList;
    }
}
