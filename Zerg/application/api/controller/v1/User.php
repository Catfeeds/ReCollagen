<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\User as UserModel;
use app\api\service\Token;

use app\lib\exception\UserException;
use think\Controller;

class User extends BaseController
{
    protected $beforeActionList = [
        'checkPrimaryScope' => ['only' => 'getUserAccount']
    ];
    
    /**
     * 获取用户账户信息
     */
    public function getUserAccount(){
        $uid = Token::getCurrentUid();

        $user = UserModel::field('mainAccount,secondAccount')->where('uid', $uid)
            ->find();
        if(!$user){
            throw new UserException();
        }

        return $user;
    }

}