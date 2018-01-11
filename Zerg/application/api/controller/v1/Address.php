<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\User;
use app\api\model\UserAddress;
use app\api\service\Token;
use app\api\service\Token as TokenService;
use app\api\validate\AddressNew;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;
use think\Controller;
use think\Exception;

class Address extends BaseController
{
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'createAddress,updateAddress,delAddress,getUserAddress']
    ];

    /**
     * 获取用户地址信息
     * @return false|\PDOStatement|string|\think\Collection
     * @throws UserException
     * @throws \app\lib\exception\ParameterException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUserAddress(){
        $uid = Token::getCurrentUid();

        $userAddress = UserAddress::field('address_id,name,telephone,province,city,country,address,is_default')->where('uid', $uid)
            ->select();
        if(!$userAddress){
            throw new UserException([
               'msg' => '用户地址不存在',
                'errorCode' => 60001
            ]);
        }

        return $userAddress;
    }
    /**
     * 创建用户收货地址
     */
    public function createAddress(){

        $validate = new AddressNew();
        $validate->goCheck();

        $uid = TokenService::getCurrentUid();
//        $uid = 2;
        $user = User::get($uid);
        if(!$user){
            throw new UserException();
        }
        // 根据规则取字段是很有必要的，防止恶意更新非客户端字段
        $data = $validate->getDataByRule(input('post.'));
        $data['province_id'] = UserAddress::getProvinceId($data['province']);
        $data['city_id'] = UserAddress::getCityId($data['city']);

        //如果设为默认且已有默认地址，改为普通地址
        if ($data['is_default'] == 1) {
            UserAddress::where(['uid'=>$uid,'is_default'=>1])->update(['is_default'=>-1]);
        }

        $user->address()->save($data);

        return new SuccessMessage();
    }

    /**
     * 更新用户收货地址
     */
    public function updateAddress(){

        $validate = new AddressNew();
        $validate->goCheck();

        $uid = TokenService::getCurrentUid();
//                $uid = 2;
        $data = input('post.');

        $userAddress = UserAddress::get($data['address_id']);
        if (!$userAddress) {
            throw new UserException(
                [
                    'msg' => '用户收货地址不存在',
                    'errorCode' => 60001,
                ]);
        }

        $data['province_id'] = UserAddress::getProvinceId($data['province']);
        $data['city_id'] = UserAddress::getCityId($data['city']);

        //如果设为默认且已有默认地址，改为普通地址
        if ($data['is_default'] == 1) {
            UserAddress::where(['uid'=>$uid,'address_id'=>['neq',$data['address_id']],'is_default'=>1])->update(['is_default'=>-1]);
        }
        $userAddress->save($data);

        return new SuccessMessage();
    }
    /**
     * 删除用户收货地址
     */
    public function delAddress($id){

        UserAddress::destroy($id);

        return new SuccessMessage();
    }

}