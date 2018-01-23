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
        'checkPrimaryScope' => ['only' => 'getUserData']
    ];

    /**
     * 获取用户账户信息
     */
    public function getUserData() {
        $uid = Token::getCurrentUid();
//        $uid = 2;

        $user = UserModel::field('mainAccount,secondAccount,checked,uname,uwecat,usex,utel,uemail,up_name,up_wecat,IDcode,IDcode_pic,IDcode_pic_b,IDcode_pic_h')
            ->where('uid', $uid)->find();
        if (!$user) {
            throw new UserException();
        }
        $user['IDcode_pic'] = $user['IDcode_pic'] ? config('setting.img_prefix').$user['IDcode_pic'] : '';
        $user['IDcode_pic_b'] = $user['IDcode_pic_b'] ? config('setting.img_prefix').$user['IDcode_pic_b'] : '';
        $user['IDcode_pic_h'] = $user['IDcode_pic_h'] ? config('setting.img_prefix').$user['IDcode_pic_h'] : '';

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
     * 上传视频
     */
    public function uploadUserPic() {
        halt($_FILES);
        if($_SERVER['REQUEST_METHOD'] == "POST"){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->rootPath  =     'Uploads/video/file/'; // 设置附件上传根目录
            $upload->savePath  =     ''; // 设置附件上传（子）目录+
            // 上传文件
            $info   =   $upload->upload();
            if(!$info) {// 上传错误提示错误信息
                $this->error('上传失败！');
            }else{// 上传成功
                $url  = $info['file']['savepath'].$info['file']['savename'];

                $data['code']   = 0;
                $data['msg']    = '上传成功';
                $data['uploadFileName'] = $info['file']['name'];
                $data['uploadFileUrl']  = $upload->rootPath.$url;
                return  json_encode($data);
                // $this->success(['uploadFileName'=>$info['file']['name'],'uploadFileSize'=>$size,'uploadFileUrl'=>$upload->rootPath.$url]);
            }
        }
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
