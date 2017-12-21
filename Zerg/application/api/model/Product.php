<?php

namespace app\api\model;

use think\Model;

class Product extends BaseModel
{   
    protected $table = 'osc_goods';
    protected $autoWriteTimestamp = 'datetime';
    protected $hidden = [
        'pivot','sale_count','status', 'sort_order', 'create_time', 'update_time'
    ];

    /**
     * 获取商品选项
     */
    public function options(){
        return $this->hasMany('ProductOption', 'goods_id', 'goods_id');
    }
    /**
     * 获取商品折扣
     */
    public function discounts(){
        return $this->hasMany('ProductDiscount', 'goods_id', 'goods_id');
    }
    /**
     * 获取商品详情页滚动图
     */
    public function imgs(){
        return $this->hasMany('ProductImage', 'goods_id', 'goods_id');
    }
    /**
     * 获取商品详情图片
     */
    public function detail(){
        return $this->hasMany('ProductDetail', 'goods_id', 'goods_id');
    }
    /**
     * 修改图片路径
     */
    public function getImageAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }

    /**
     * 获取产品参数
     */
    public function properties(){
        return $this->hasMany('ProductProperty', 'goods_id', 'goods_id');
    }

    /**
     * 获取某分类下商品
     * @param $categoryID
     * @param int $page
     * @param int $size
     * @param bool $paginate
     * @return \think\Paginator
     */
    public static function getProductsByCategoryID($categoryID, $paginate = true, $page = 1, $size = 30){
        
        $query = self::where('cat_id', '=', $categoryID);
        if (!$paginate){
            return $query->order('create_time desc')->select();
        }else{
            // paginate 第二参数true表示采用简洁模式，简洁模式不需要查询记录总数
            return $query->paginate(
                $size, true, [
                'page' => $page
            ]);
        }
    }

    /**
     * 获取商品详情
     * @param $id
     * @return null | Product
     */
    public static function getProductDetail($id)
    {
        //千万不能在with中加空格,否则你会崩溃的
        //        $product = self::with(['imgs' => function($query){
        //               $query->order('index','asc');
        //            }])
        //            ->with('properties,imgs.imgUrl')
        //            ->find($id);
        //        return $product;

        $product = self::with(
                ['options' => function ($query){
                        $query->order('sort');
                }])
            ->with('discounts')
            ->with(
                ['imgs' => function ($query){
                        $query->order('sort_order');
                }])
            ->with(
                ['detail' => function ($query){
                    $query->order('sort_order');
                }])
            ->with('properties')
            ->find($id);
        return $product;
    }
    /**
     * 获取最近商品
     */
    public static function getMostRecent(){

        $products = self::with(
                ['options' => function ($query){
                        $query->order('sort');
                }])
            ->where(['status'=>1])
            ->order('create_time desc')
            ->select();
        //如果商品有选项，默认价格为第一个选项的价格
        if (!empty($products)) {
            foreach ($products as $key => $product) {
                if (!empty($product['options'][0])) {
                    $products[$key]['price'] = $product['options'][0]['option_price'];
                }
                unset($product['options']);
            }
        }
        return $products;
    }

}
