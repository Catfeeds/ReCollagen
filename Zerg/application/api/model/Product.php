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
     * 图片属性
     */
    public function imgs(){
        return $this->hasMany('ProductImage', 'product_id', 'id');
    }
    /**
     * 修改图片路径
     */
    public function getImageAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }


    public function properties(){
        return $this->hasMany('ProductProperty', 'product_id', 'id');
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
            [
                'imgs' => function ($query)
                {
                    $query->with(['imgUrl'])
                        ->order('order', 'asc');
                }])
            ->with('properties')
            ->find($id);
        return $product;
    }

    public static function getMostRecent($page,$size){

        $products = self::order('add_time desc')->where(['status'=>1])->paginate($size, true, ['page' => $page]);

        return $products;
    }

}
