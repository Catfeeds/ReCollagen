<?php

namespace app\api\model;

use think\Model;

class Product extends BaseModel
{   
    protected $table = 'osc_goods';
    protected $autoWriteTimestamp = 'datetime';
    protected $hidden = [
        'pivot', 'add_time', 'date_modified', 'viewed','status', 'sort_order', 'location', 'shipping', 'sku', 'brand_id', 'date_available', 'stock_status_id', 'weight_class_id', 'length_class_id', 'subtract', 'minimum', 'pay_points', 'is_points_goods', 'model', 'points', 'sale_count'
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
            return $query->select();
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

    public static function getMostRecent($page,$size){

        $products = self::where(['status'=>1])->order('add_time desc')->paginate($size, true, ['page' => $page]);

        return $products;
    }

}
