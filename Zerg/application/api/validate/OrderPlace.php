<?php

namespace app\api\validate;


use app\lib\exception\ParameterException;
use think\Exception;

class OrderPlace extends BaseValidate
{
    protected $rule = [
        'goodsArrInfo'    => 'require|checkProducts',
        'mainGoodsPrice'  => 'require|float',
        'otherGoodsPrice' => 'require|float',
        'shippingPrice'   => 'float'
    ];
    protected $message = [
        'goodsArrInfo.require'    => '商品列表不能为空',
        'mainGoodsPrice.require'  => '主商品价格不能为空',
        'mainGoodsPrice.float'    => '主商品价格必须是数字',
        'otherGoodsPrice.require' => '辅销品价格不能为空',
        'otherGoodsPrice.float'   => '辅销品价格必须是数字',
        'shippingPrice.float'     => '运费必须是数字',
    ];

    protected $singleRule = [
        'goods_id'  => 'require|isPositiveInteger',
        'count'     => 'require|isPositiveInteger',
        'option_id' => 'isPositiveInteger',
    ];
    /**
     * 验证商品
     */
    protected function checkProducts($values){
        foreach ($values as $value){
            $this->checkProduct($value);
        }
        return true;
    }
    /**
     * 逐一验证商品参数
     */
    private function checkProduct($value){
        $validate = new BaseValidate($this->singleRule);
        $result = $validate->check($value);
        if(!$result){
            throw new ParameterException([
                'msg' => '商品列表参数错误',
            ]);
        }
    }

}