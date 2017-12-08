<?php
/**
 * 会员编辑验证
 */
namespace osc\member\validate;
use think\Validate;
class Member extends Validate
{
    protected $rule = [
        // 'username'  =>  'require|min:2|unique:member',
        // 'password'  =>  'require|min:6',
        // 'email'     =>  'unique:member',
        // 'telephone' =>  'unique:member',
        'mainAccount'   => 'number|egt:0',
        'secondAccount'   => 'number|egt:0',

    ];

    protected $message = [
        // 'username.require'  =>  '用户名必填',
        // 'username.min'      =>  '用户名不能小于两个字',     
        // 'username.unique'   =>  '用户名已经存在',
        // 'password.require'  =>  '密码必填',
        // 'password.min'      =>  '密码不能小于6位',  	
        // 'email.unique'      =>  '邮箱已经存在',
        // 'telephone.unique'  =>  '手机号码已经存在',

        'mainAccount.number' =>  '主账户金额必须为数字',
        'mainAccount.egt'    =>  '主账户金额最小值为0',
        'secondAccount.egt'  =>  '辅账户金额必须为数字',
        'secondAccount.egt'  =>  '辅账户金额最小值为0',
    ];
	
	protected $scene = [
        'edit'  =>  ['mainAccount','secondAccount'],
    ];
	
}
?>