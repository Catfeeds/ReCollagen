<?php
/**
 * 会员编辑验证
 */
namespace osc\member\validate;
use think\Validate;
class Member extends Validate
{
    protected $rule = [
        'mainAccount'   => 'number|egt:0',
        'secondAccount'   => 'number|egt:0',

    ];

    protected $message = [
        'mainAccount.number' =>  '主账户金额必须为数字',
        'mainAccount.egt'    =>  '主账户金额最小值为0',
        'secondAccount.egt'  =>  '辅账户金额必须为数字',
        'secondAccount.egt'  =>  '辅账户金额最小值为0',
    ];
	
}
?>