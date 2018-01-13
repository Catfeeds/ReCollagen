<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\User as UserModel;
use app\api\service\Token;
use app\lib\exception\UserException;
use app\lib\exception\SuccessMessage;
use think\Controller;

class User extends BaseController {

    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'getUserAccount,getUserData']
    ];

    /**
     * 获取用户账户信息
     */
    public function getUserAccount() {
        $uid = Token::getCurrentUid();

        $user = UserModel::field('mainAccount,secondAccount')->where('uid', $uid)
                ->find();
        if (!$user) {
            throw new UserException();
        }

        return $user;
    }

    /**
     * 获取用户账户信息
     */
    public function getUserData() {
        $uid = Token::getCurrentUid();

        $user = UserModel::get($uid);
        if (!$user) {
            throw new UserException();
        }

        return $user;
    }

    /**
     * 更新用户账户信息
     */
    public function getUserUpdate() {
        $uid = Token::getCurrentUid();
        $data = input('post.');
        $user = UserModel::where('uid', $uid)
                ->update($data);
        if (!$user) {
            throw new UserException(
                [
                    'msg' => '用户ID不存在',
                    'errorCode' => 60001,
                ]);
        }

        return new SuccessMessage();
    }

}
