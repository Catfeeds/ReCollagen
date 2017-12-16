<?php
namespace osc\member\validate;
use think\Validate;
class OrderStatus extends Validate
{
    protected $rule = [
        'name'  =>  'require|min:2|unique:order_status',
          
    ];

    protected $message = [
        'name.require'  =>  '名称必填',
        'name.min'  =>  '名称不能小于两个字',     
        'name.unique'  =>  '名称已存在',         
		
    ];

	
}
?>