<?php

namespace osc\member\controller;

use osc\common\controller\AdminBase;
use think\Db;

class OrderBackend extends AdminBase {

    protected function _initialize() {
        parent::_initialize();
        $this->assign('breadcrumb1', '订单');
        $this->assign('breadcrumb2', '订单');
    }

    /**
     * 订单列表
     */
    public function index() {
        $params = input('param.');
        $params['isHistory'] = 0;

        $this->assign('list', osc_order()->order_list($params, 15));
        $this->assign('empty', '<tr><td colspan="20">没有数据</td></tr>');
        $this->assign('params', $params);

        return $this->fetch();
    }
    /**
     * 历史订单列表
     */
    public function history() {
        $params = input('param.');
        $params['isHistory'] = 1;

        $this->assign('list', osc_order()->order_list(input('param.'), 15));
        $this->assign('empty', '<tr><td colspan="20">没有数据</td></tr>');
        $this->assign('params', $params);

        return $this->fetch();
    }
    /**
     * 导出订单
     */
    public function toExport() {
        $params = input('');
        osc_order()->toExport($params);
    }

    /**
     * 订单详情
     */
    public function show_order() {
        if (request()->isPost()) {
            $data = input('post.');
//halt($data);
            $mainGoodsPrice = 0;
            $otherGoodsPrice = 0;
            $shippingPrice = $data['shippingPrice'];
            $app = Db::name('order_goods')->where('order_id', $data['order_id'])->select();
            foreach ($app as $m => $n) {
                $quantity = $data['quantity' . $n["order_goods_id"]];
                $price = $data['price' . $n["order_goods_id"]];
                $totals = $quantity * $price;
                if ($totals <= 0) {
                    $this->error('商品价格必须大于0');
                }
                if ($n["isMainGoods"] == 1) {
                    $mainGoodsPrice = $mainGoodsPrice + $totals;
                } elseif ($n["isMainGoods"] == 0) {
                    $otherGoodsPrice = $otherGoodsPrice + $totals;
                }
                $data3 = ['price' => $price, 'total' => $totals];
                Db::name('order_goods')->where('order_goods_id', $n['order_goods_id'])->update($data3);
            }

            $total = $mainGoodsPrice + $otherGoodsPrice + $shippingPrice;
            $data2 = ['mainGoodsPrice' => $mainGoodsPrice, 'otherGoodsPrice' => $otherGoodsPrice, 'shippingPrice' => $shippingPrice, 'total' => $total];

            if (Db::name('order')->where('order_id', $data['order_id'])->update($data2)) {
                storage_user_action(UID, session('user_auth.username'), config('BACKEND_USER'), '编辑了订单价格');
                $this->success('编辑成功', url('OrderBackend/show_order',array("id"=>$data['order_id'])));
            } else {
                $this->error('编辑失败');
            }
        }

        $info = osc_order()->order_info(input('param.id/d'));

        $info['order']['transportInfo'] = '';
        if (!empty($info['order']['shipping_method']) && !empty($info['order']['shipping_num'])) {
            //获取快递公司代码
            $shippingCode = getShippingCode($info['order']['shipping_method']);
            //查询物流进度
            $info['order']['transportInfo'] = getTransInfo($shippingCode, $info['order']['shipping_num']);
        }

        $info['order']['promotion'] = json_decode($info['order']['promotion'], true);
        //快递公司
        $info['trans'] = Db::name('transport')->select();

        $this->assign('data', $info);
        $this->assign('crumbs', '订单详情');

        return $this->fetch('show');
    }

    /**
     * 打印订单
     */
    public function print_order() {
        $order = osc_order()->order_info(input('param.id/d'));
        $order['order']['promotion'] = json_decode($order['order']['promotion'], true);

        $this->assign('order', $order);
        return $this->fetch('order');
    }

    /**
     * 删除订单
     */
    public function del() {
        osc_order()->del_order((int) input('param.id'));
        storage_user_action(UID, session('user_auth.username'), config('BACKEND_USER'), '删除了订单');
        $this->redirect('OrderBackend/index');
    }
    /**
     * 取消订单
     */
    function cancel(){
        osc_order()->cancel_order((int)input('param.id'));
        storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'取消了订单');
        $this->success('取消成功');
    }

    public function update_order() {
        $data = input('post.');
        $type = input('param.type');

        //更新 order_goods
        $og = Db::name('order_goods')->find($data['order_goods_id']);

        if ($type == 'quantity') {

            $update['quantity'] = $data['quantity'];
            $update['total'] = $data['quantity'] * $og['price'];
            $update['order_goods_id'] = $data['order_goods_id'];
        } elseif ($type == 'price') {

            $update['price'] = $data['price'];
            $update['total'] = $og['quantity'] * $data['price'];
            $update['order_goods_id'] = $data['order_goods_id'];
        }

        if (Db::name('order_goods')->update($update, false, true)) {

            $total = 0;
            //更新 order
            $order_goods = Db::name('order_goods')->where(array('order_id' => $data['order_id']))->select();

            foreach ($order_goods as $k => $v) {
                $total += $v['total'];
            }

            Db::name('order')->where(array('order_id' => $data['order_id']))->update(array('total' => $total + $data['shipping']));

            storage_user_action(UID, session('user_auth.username'), config('BACKEND_USER'), '更新了订单');

            return true;
        }
    }
    /**
     * 修改卖家备注
     */
    public function updateShopRemarks() {

        $data = input('');
        $res = Db::name('order')->where(['order_id' => $data['id']])->update(['shopRemarks' => $data['shopRemarks']]);

        if ($res) {
            storage_user_action(UID, session('user_auth.username'), config('BACKEND_USER'), '更新了卖家备注');
            return true;
        }
        return false;
    }

    /**
     * 更新物流信息
     */
    public function update_shipping() {
        $data = input('');
        $res = Db::name('order')->where(['order_id' => $data['id']])
            ->update(['order_status' => 3, 'shipping_num' => $data['shipping_num'],'shipping_method' => $data['shipping_method'], 'deliver_time' => date('Y-m-d H:i:s'), 'update_time' => time()]);
        if ($res) {
            storage_user_action(UID, session('user_auth.username'), config('BACKEND_USER'), '更新了物流单号');
            return true;
        }
        return false;
    }

}

?>