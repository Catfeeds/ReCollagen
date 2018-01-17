<?php
namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\Cart as CartModel;
use app\api\service\Token as TokenService;
use app\lib\exception\ProductException;
use app\lib\exception\SuccessMessage;

class Cart extends BaseController
{
    /**
     * 添加到购物车
     * @return SuccessMessage
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addCart(){

        $params = input('post.');
        $cartModel = new CartModel();

        $currentUid = TokenService::getCurrentUid();
//        $currentUid = 2;

        $where = ['uid'=>$currentUid,'goods_id'=>$params['goods_id'],'goods_option_id'=>$params['goods_option_id']];

        $haveAdd = $cartModel->where($where)->find();
        if (!$haveAdd) {
            $cartModel->save($where);
        }else{
            $cartModel->where($where)->setInc('count');
        }
        return new SuccessMessage();
    }

    /**
     * 从购物车删除商品
     * @return SuccessMessage
     * @throws \app\lib\exception\ParameterException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function delCart(){

        $params = input('post.');
        $cartModel = new CartModel();

        $currentUid = TokenService::getCurrentUid();
//        $currentUid = 2;

        $where = ['uid'=>$currentUid,'goods_id'=>$params['goods_id'],'goods_option_id'=>$params['goods_option_id']];

        $haveAdd = $cartModel->where($where)->find();
        if ($haveAdd) {
            CartModel::destroy($haveAdd['id']);
        }
        return new SuccessMessage();
    }

    /**
     * 点击增加或减少商品数量
     * @return SuccessMessage
     * @throws ProductException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setCountByClick(){

        $params = input('post.');
        $cartModel = new CartModel();

        $currentUid = TokenService::getCurrentUid();
//        $currentUid = 2;

        $where = ['uid'=>$currentUid,'goods_id'=>$params['goods_id'],'goods_option_id'=>$params['goods_option_id']];

        $haveAdd = $cartModel->where($where)->find();
        if ($haveAdd) {
            if ($params['type'] == 'inc') {
                $cartModel->where($where)->setInc('count');
            }elseif($params['type'] == 'dec') {
                $cartModel->where($where)->setDec('count');
            }
        }else{
            throw new ProductException();
        }
        return new SuccessMessage();
    }

    /**
     * 输入数字修改购物车商品数量
     * @return SuccessMessage
     * @throws ProductException
     * @throws \app\lib\exception\ParameterException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setCountByInput(){

        $params = input('post.');
        $cartModel = new CartModel();

        $currentUid = TokenService::getCurrentUid();
//        $currentUid = 2;

        $where = ['uid'=>$currentUid,'goods_id'=>$params['goods_id'],'goods_option_id'=>$params['goods_option_id']];

        $haveAdd = $cartModel->where($where)->find();
        if ($haveAdd) {
            $cartModel->save(['count'=>$params['count']],$where);
        }else{
            throw new ProductException();
        }
        return new SuccessMessage();
    }

    /**
     * 设置购物车商品选中状态
     * @return SuccessMessage
     * @throws ProductException
     * @throws \app\lib\exception\ParameterException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function setChecked(){

        $params = input('post.');
        $cartModel = new CartModel();

        $currentUid = TokenService::getCurrentUid();
//        $currentUid = 2;

        $where = ['uid'=>$currentUid,'goods_id'=>$params['goods_id'],'goods_option_id'=>$params['goods_option_id']];

        $haveAdd = $cartModel->where($where)->find();
        if ($haveAdd) {
            $isChecked = $haveAdd['isChecked'] == 1 ? -1 : 1;
            $cartModel->save(['isChecked'=>$isChecked],$where);
        }else{
            throw new ProductException();
        }
        return new SuccessMessage();
    }

    /**
     * 批量设置购物车商品选中状态
     * @return SuccessMessage
     * @throws \app\lib\exception\ParameterException
     */
    public function batchSetChecked(){

        $params = input('post.');
        $cartModel = new CartModel();

        $currentUid = TokenService::getCurrentUid();
//        $currentUid = 2;

        $where = ['uid'=>$currentUid];
        $cartModel->save(['isChecked'=>$params['isCheck']],$where);
        return new SuccessMessage();
    }

    /**
     * 获取购物车商品
     * @return array
     * @throws \app\lib\exception\ParameterException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getCartGoods(){

        $currentUid = TokenService::getCurrentUid();
//        $currentUid = 2;

        $goodsList = CartModel::getCartGoodsByUid($currentUid);

        if ($goodsList->isEmpty()){
            return [];
        }
        $data = $goodsList->hidden(
            [
                'id','uid'
            ])
            ->toArray();
        return $data;
    }

}