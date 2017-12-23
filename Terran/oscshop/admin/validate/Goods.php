<?php
namespace osc\admin\validate;
use think\Validate;
class Goods extends Validate
{
    protected $rule = [
        'image'  =>  'require',
        'name'   =>  'require|min:2|unique:goods',  
        'cat_id' =>  'require',
        'price'  =>  'require|float|egt:0.01',  
        'stock'  =>  'require|number|egt:0',  
        'weight' =>  'float|egt:1',  
        'bulk'   =>  'float|egt:0.01',  
    ];

    protected $message = [
        'image.require'  =>  '请上传商品缩略图',
    
        'name.require'   =>  '商品名称必填',
        'name.min'       =>  '商品名称不能小于两个字',  
        'name.unique'    =>  '商品名称已经存在',     
        
        'cat_id.require' =>  '商品分类必选',
        
        'price.require'  =>  '商品价格必填',
        'price.float'    =>  '商品价格必须是数字',
        'price.egt'      =>  '商品价格最低为0.01',
        
        'stock.require'  =>  '库存数量必填',
        'stock.number'   =>  '库存数量必须是数字',
        'stock.egt'      =>  '库存数量最低为0',
        
        'weight.float'   =>  '重量必须是数字',
        'weight.egt'     =>  '重量最低为1',
        'bulk.float'     =>  '体积必须是数字',
        'bulk.egt'       =>  '体积最低为0.01',
    ];

}
?>