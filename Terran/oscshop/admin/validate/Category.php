<?php
namespace osc\admin\validate;
use think\Validate;
class Category extends Validate
{
    protected $rule = [
        'image'  =>  'require',
        'name'  =>  'require|min:2|unique:category',
       
    ];

    protected $message = [
        'image.require'=>  '请上传分类展示图片',

        'name.require' =>  '请填写分类名',
        'name.min'     =>  '分类名不能小于两个字',     
        'name.unique'  =>  '分类名已存在', 
       
    ];

	
}
?>