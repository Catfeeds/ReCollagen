<?php

namespace osc\member\controller;

use osc\common\controller\AdminBase;
use think\Db;

/**
 * 会员管理
 */
class MemberBackend extends AdminBase {

    /**
     * 初始化
     */
    protected function _initialize() {
        parent::_initialize();
        $this->assign('breadcrumb1', '会员');
        $this->assign('breadcrumb2', '会员管理');
    }

    /**
     * 会员列表
     */
    public function index() {
        $param = input('param.');
        $map = $query = [];
        if (isset($param['condition'])) {
            $map['m.openId|a.name|a.telephone'] = ['like', "%" . trim($param['condition']) . "%"];
        }

        $list = Db::name('member')->alias('m')->field('m.*,a.name,a.telephone')
            ->join('__ADDRESS__ a', 'a.uid=m.uid and a.is_default =1', 'left')
            ->where($map)
            ->order('m.create_time desc')
            ->paginate(config('page_num'));

        $this->assign('list', $list);
        $this->assign('empty', '<tr><td colspan="20">没有数据</td></tr>');

        return $this->fetch();
    }

    /**
     * 编辑会员
     */
    public function edit() {
        if (request()->isPost()) {
            $data = input('post.');
//            $result = $this->validate($data, 'Member.edit');
//            if ($result !== true) {
//                $this->error($result);
//            }
            $data['update_time'] = time();

            $u = Db::name('member')->where('uid', $data['uid'])->find();
            $omainAccount = $u['mainAccount'];
            if ((float) $data['mainAccount'] != 0) {
                if ($data['sma'] == 1) {
                    $nmainAccount = $omainAccount + $data['mainAccount'];
                    finance_record($data['uid'], '站内', $data['mainAccount'], $nmainAccount, time(), '后台操作-增加', session('user_auth.username'), 1, 0);
                } elseif ($data['sma'] == 2) {
                    $nmainAccount = $omainAccount - $data['mainAccount'];
                    finance_record($data['uid'], '站内', -$data['mainAccount'], $nmainAccount, time(), '后台操作-扣除', session('user_auth.username'), 1, 0);
                }
            } else {
                $nmainAccount = $omainAccount;
            }

            $osecondAccount = $u['secondAccount'];
            if ((float) $data['secondAccount'] != 0) {
                if ($data['ssa'] == 1) {
                    $nsecondAccount = $osecondAccount + $data['secondAccount'];
                    finance_record($data['uid'], '站内', $data['secondAccount'], $nsecondAccount, time(), '后台操作-增加', session('user_auth.username'), 2, 0);
                } elseif ($data['ssa'] == 2) {
                    $nsecondAccount = $osecondAccount - $data['secondAccount'];
                    finance_record($data['uid'], '站内', -$data['secondAccount'], $nsecondAccount, time(), '后台操作-扣除', session('user_auth.username'), 2, 0);
                }
            } else {
                $nsecondAccount = $osecondAccount;
            }

            $data2 = ['mainAccount' => $nmainAccount, 'secondAccount' =>  $nsecondAccount, 'checked' =>  $data['checked'], 'update_time' =>  $data['update_time']];
            //print_r($data2);
            if (Db::name('member')->where('uid', $data['uid'])->update($data2)) {
                storage_user_action(UID, session('user_auth.username'), config('BACKEND_USER'), '编辑了会员');
                $this->success('编辑成功', url('MemberBackend/index'));
            } else {
                $this->error('编辑失败');
            }
        }
        $uid = input('param.id/d');
        $data = Db::name('member')->alias('m')->field('m.*,a.name,a.telephone,a.province,a.city,a.country,a.address')
            ->join('__ADDRESS__ a', 'a.uid=m.uid and a.is_default =1', 'left')
            ->where(['m.uid' => $uid])
            ->find();
        $address = Db::name('address')->where('uid', $uid)->order('is_default desc')->select();

        $this->assign('data', $data);
        $this->assign('address', $address);
        $this->assign('crumbs', '会员资料');
        return $this->fetch('info');
    }

    /**
     * 修改用户账户金额
     */
    public function updateAccount() {
        $data = input('post.');
        $update['uid'] = (int) $data['uid'];
        $update[$data['accountName']] = (float) $data['accountMoney'];
        $u = Db::name('member')->where('uid', $data['uid'])->field();
        $oprice = $u[$data['accountName']];
        $nprice = (float) $data['accountMoney'];
        $cprice = $nprice - $oprice;
        if ($data['accountName'] == 'mainAccount') {
            $rectype = 1;
        } elseif ($data['accountName'] == 'secondAccount') {
            $rectype = 2;
        }
        if (Db::name('member')->update($update)) {
            storage_user_action(UID, session('user_auth.username'), config('BACKEND_USER'), '更新会员账户金额');
            finance_record((int) $data['uid'], '站内', $cprice, $nprice, time(), '后台操作', session('user_auth.username'), $rectype, 0);
            return true;
        }
        return false;
    }

    /**
     * 修改用户状态
     */
    public function set_status() {
        $data = input('param.');
        Db::name('member')->where('uid', $data['uid'])->update(['checked' => (int) $data['checked']], false, true);

        storage_user_action(UID, session('user_auth.username'), config('BACKEND_USER'), '修改了用户状态');

        $this->redirect('MemberBackend/index');
    }

    /**
     * 财务流水
     */
    public function finance_list() {
        $param = input('param.');
        $map = $query = [];
        if (isset($param['condition'])) {
            $map['m.openId|f.reason|f.editor'] = ['like', "%" . trim($param['condition']) . "%"];
        }
        if (isset($param['rectype'])) {
            $map['f.rectype'] = ['=', trim($param['rectype'])];
        }

        $list = Db::name('finance_record')->alias('f')->field('f.*,m.openId')
            ->join('__MEMBER__ m', 'm.uid=f.uid', 'left')
            ->where($map)
            ->order('f.itemid desc')
            ->paginate(config('page_num'));

        $this->assign('list', $list);
        $this->assign('rectype', input('get.rectype/d'));
        $this->assign('empty', '<tr><td colspan="20">没有数据</td></tr>');

        return $this->fetch();
    }

}

?>