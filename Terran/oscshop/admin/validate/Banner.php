<?php
namespace osc\admin\validate;
use think\Validate;

class Banner extends Validate
{
    protected $rule = [
        'goods_id' => 'require|number|checkID',
        'sort'     => 'number',
        'image'    => 'require'
    ];

    protected $message = [
        'goods_id.require' =>  '请输入需要链接到的商品ID', 
        'goods_id.number'  =>  '商品ID必须是数字',         
        'goods_id.checkID' =>  '商品不存在',         
        'sort.number'      =>  '排序值必须是数字',
        'image.require'    =>  '请上传banner图片',
    ];

    protected function checkID($value){
        $product = getGoodsByGoodsId($value);
        if (!$product) {
            return false;
        }
        return true;
    }

	
}
?>