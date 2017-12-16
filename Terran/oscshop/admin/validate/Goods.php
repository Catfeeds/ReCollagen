<?php
namespace osc\admin\validate;
use think\Validate;
class Goods extends Validate
{
    protected $rule = [
        'name'     =>  'require|min:2|unique:goods',  
        'cat_id'   =>  'require',
        'price'    =>  'require|float|between:0.01,9999999',  
        'stock' =>  'require|number|between:0,99999999',  
        'weight'   =>  'float|between:1,9999999',  
        'length'   =>  'float|between:1,9999999',  
        'width'    =>  'float|between:1,9999999',  
        'height'   =>  'float|between:1,9999999',  
        'image'    =>  'require',
    ];

    protected $message = [
        'name.require'     =>  '商品名称必填',
        'name.min'         =>  '商品名称不能小于两个字',  
        'name.unique'      =>  '商品名称已经存在',     
        
        'cat_id.require'   =>  '商品分类必选',
        
        'price.require'    =>  '商品价格必填',
        'price.float'      =>  '商品价格必须是数字',
        'price.between'    =>  '商品价格最低为0.01',
        
        'stock.require' =>  '库存数量必填',
        'stock.number'  =>  '库存数量必须是数字',
        'stock.between' =>  '库存数量最低为0',
        
        'weight.float'     =>  '重量必须是数字',
        'weight.between'   =>  '重量最低为1',
        'length.float'     =>  '长度必须是数字',
        'length.between'   =>  '长度最低为1',
        'width.float'      =>  '宽度必须是数字',
        'width.between'    =>  '宽度最低为1',
        'height.float'     =>  '高度必须是数字',
        'height.between'   =>  '高度最低为1',
        
        'image.require'    =>  '请上传商品缩略图',

    ];
	
	 protected $scene = [
        // 'edit'  =>  ['user_name'=>'require|min:2'],
    ];
	
}
?>