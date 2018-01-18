<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\FinanceRecord;
use app\api\model\User as UserModel;
use app\api\service\Token;
use app\lib\exception\UserException;
use app\lib\exception\SuccessMessage;
use app\api\validate\PagingParameter;

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
     * 修改用户信息
     */
    public function editUserData() {
        $uid = Token::getCurrentUid();
//        $uid = 2;
        $data = input('post.');
        $model = new UserModel();
        $res = $model->save($data,['uid'=>$uid]);

        if (!$res) {
            throw new UserException(
                [
                    'msg' => '修改失败',
                    'errorCode' => 60005,
                ]);
        }

        return new SuccessMessage();
    }
    /**
     * 分页获取用户财务流水
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getAccountRecord($page = 1, $size = 20)
    {
        (new PagingParameter())->goCheck();

        $uid = Token::getCurrentUid();
//        $uid = 2;

        $pagingData = FinanceRecord::getAccountRecordByPage($uid, $page, $size);

        return $pagingData;
    }

}
