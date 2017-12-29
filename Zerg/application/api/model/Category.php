<?php

namespace app\api\model;

use think\Model;

class Category extends BaseModel{
    
    protected $hidden = ['sort', 'update_time', 'meta_keyword', 'meta_description'];

    /**
     * 修改图片路径
     */
    public function getImageAttr($value, $data){
        return $this->prefixImgUrl($value, $data);
    }

    public function products()
    {
        return $this->hasMany('Product', 'category_id', 'id');
    }

    // public function img()
    // {
    //     return $this->belongsTo('Image', 'topic_img_id', 'id');
    // }
    /**
     * 获取所有商品分类
     */
    public static function getAllCategories(){
        $categories = self::where(['pid'=>0])->order('sort')->select();
        if (!empty($categories)) {
            foreach ($categories as $k => $parent) {
                $child = self::where(['pid'=>$parent['id']])->order('sort')->select();
                $categories[$k]['child'] = $child;

            }
        }
        return $categories;
    }
    public static function getCategories($ids)
    {
        $categories = self::with('products')
            ->with('products.img')
            ->select($ids);
        return $categories;
    }
    
    public static function getCategory($id)
    {
        $category = self::with('products')
            ->with('products.img')
            ->find($id);
        return $category;
    }
}
