<?php
namespace osc\admin\validate;
use think\Validate;

class Promotion extends Validate
{
    protected $rule = [
        ['name','require|unique:promotion','请输入促销活动名称|促销活动名称重复'],
        ['type','require','请输入促销活动类型'],
        ['money','require','请输入需要满足的金额'],
        ['money','float','金额必须是数字'],
        // ['expression','require','请输入折扣/优惠金额'],
        ['expression','number','折扣/优惠金额必须是数字'],
        ['start_time','require','请输入活动开始时间'],
        ['end_time','require','请输入活动结束时间'],

    ];
	
}
?>