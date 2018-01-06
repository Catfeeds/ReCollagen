<?php

namespace osc\common\service;

use think\Db;
use think\Loader;
use osc\common\Util\Think\Page;

class Order
{

    //删除订单
    public function del_order($id)
    {

        Db::name('order')->where(array('order_id' => $id))->delete();
        Db::name('order_goods')->where(array('order_id' => $id))->delete();

    }

    //取消订单
    public function cancel_order($order_id, $uid = null)
    {

        if ($uid) {
            $map['uid'] = ['eq', $uid];
        }
        $order['order_status_id'] = config('cancel_order_status_id');
        $map['order_id']          = ['eq', $order_id];
        //设置订单状态
        Db::name('order')->where($map)->update($order);

    }

    /**
     * 获取订单详情信息
     */
    public function order_info($order_id, $uid = null)
    {

        $map['o.order_id'] = ['eq', $order_id];

        if ($uid) {
            $map['m.uid'] = ['eq', $uid];
        }

        $order = Db::name('order')->alias('o')->field('o.*,m.openId,d.dispatch_title')
            ->join('__MEMBER__ m', 'o.uid = m.uid')
            ->join('__DISPATCH__ d', 'o.dispatch_id = d.id')
            ->where($map)
            ->find();

        return array(
            'order'         => $order,
            'order_product' => Db::name('order_goods')->alias('og')
                ->join('__GOODS__ g', 'og.goods_id = g.goods_id', 'left')
                ->field('og.*,g.image')->where('og.order_id', $order_id)->select()
        );

    }

    /**
     * 获取后台所有订单列表
     * @param array $param 查询参数
     * @param int $page_num 每页显示条数
     * @param int $history 是否历史订单（默认显示全部待付款，待发货和7天内已发货，1则显示历史已发货订单）
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function order_list($param = array(), $page_num = 15, $history = 0)
    {

        $map['Order.order_status'] = ['in', [1,2]];

        if (isset($param['order_num'])) {
            $map['Order.order_num_alias'] = ['eq', $param['order_num']];
        }
        if (isset($param['user_name'])) {
            $map['Order.shipping_name'] = ['like', "%" . $param['user_name'] . "%"];
        }
        if (isset($param['status'])) {
            $map['Order.order_status'] = ['eq', $param['status']];
        }
        if ($history == 1) {
            //显示历史已发货收货订单，大于等于7天
            $map['Order.order_status'] = ['in', [3,4]];
            $map['Order.create_time'] = ['lt', time()-(7*24*3600)];

            $list =Db::view('Order', '*')
                ->view('Dispatch', 'dispatch_title', 'Order.dispatch_id=Dispatch.id')
                ->where($map)
                ->order('Order.shipping_name,Order.create_time desc')
                ->paginate(15);
        }else{
            //默认显示全部待付款，待发货和7天内已发货收货订单
            //分页
            $datas = Db::name('Order')->alias('Order')->field('*,d.dispatch_title')
                ->join('__DISPATCH__ d', 'Order.dispatch_id=d.id')
                ->where($map)
                ->union(function($query){
                    $map2['Order.order_status'] = ['in', [3,4]];
                    $map2['Order.create_time'] = ['egt', time()-(7*24*3600)];

                    if (input('get.order_num/s')) {
                        $map2['Order.order_num_alias'] = ['eq', input('get.order_num/s')];
                    }
                    if (input('get.user_name/s')) {
                        $map2['Order.shipping_name'] = ['like', "%" . input('get.user_name/s') . "%"];
                    }
                    if (input('get.status/d')) {
                        $map2['Order.order_status'] = ['eq', input('get.status/d')];
                    }
                    $query->table('__ORDER__')->alias('Order')->field('*,d.dispatch_title')
                        ->join('__DISPATCH__ d', 'Order.dispatch_id=d.id')
                        ->where($map2)
                        ->order('shipping_name,create_time desc');
                })
                ->select();
            $page = new Page(count($datas),15);
            $list['page'] = $page->show();

            //清单
            $list['data'] = Db::name('Order')->alias('Order')->field('*,d.dispatch_title')
                ->join('__DISPATCH__ d', 'Order.dispatch_id=d.id')
                ->where($map)
                ->union(function($query){
                    $map2['Order.order_status'] = ['in', [3,4]];
                    $map2['Order.create_time'] = ['egt', time()-(7*24*3600)];

                    if (input('get.order_num/s')) {
                        $map2['Order.order_num_alias'] = ['eq', input('get.order_num/s')];
                    }
                    if (input('get.user_name/s')) {
                        $map2['Order.shipping_name'] = ['like', "%" . input('get.user_name/s') . "%"];
                    }
                    if (input('get.status/d')) {
                        $map2['Order.order_status'] = ['eq', input('get.status/d')];
                    }
                    $current_page = input('p/d')?input('p/d'):1;

                    $query->table('__ORDER__')->alias('Order')->field('*,d.dispatch_title')
                        ->join('__DISPATCH__ d', 'Order.dispatch_id=d.id')
                        ->where($map2)
                        ->limit(($current_page-1)*15,15)
                        ->order('shipping_name,create_time desc');
                })
                ->select();
        }

        return $list;
    }

    /**
     * 写入订单
     * @param $payment_code
     * @param array $order_data
     * @return array
     */
    public function add_order($payment_code, $order_data = array())
    {

        $data = $this->get_order_data($order_data);

        $order['uid']             = $data['uid'];
        $order['order_num_alias'] = $data['order_num_alias'];
        $order['name']            = $data['name'];

        $order['email'] = $data['email'];
        $order['tel']   = $data['tel'];

        $order['shipping_name'] = $data['shipping_name'];

        $order['shipping_city_id']     = $data['shipping_city_id'];
        $order['shipping_country_id']  = $data['shipping_country_id'];
        $order['shipping_province_id'] = $data['shipping_province_id'];
        $order['shipping_tel']         = $data['shipping_tel'];
        $order['shipping_method']      = $data['shipping_method'];
        $order['address_id']           = $data['address_id'];


        $order['comment'] = $data['comment'];

        if (isset($data['pay_type']) && $data['pay_type'] == 'points') {//积分兑换订单
            $order['order_status_id'] = config('paid_order_status_id');
            $order['points_order']    = 1;
            $order['pay_points']      = $data['total'];
            $data['total']            = 0;
            $order['pay_time']        = time();
        } else {
            $order['order_status_id'] = config('default_order_status_id');
        }

        $order['ip'] = get_client_ip();

        $order['date_added'] = time();
        $order['total']      = $data['total'];
        $order['user_agent'] = $data['user_agent'];


        $order['payment_code'] = $payment_code;


        $order['pay_subject']   = isset($data['pay_subject']) ? $data['pay_subject'] : '';
        $order['return_points'] = isset($data['return_points']) ? $data['return_points'] : '';

        $order_id = Db::name('Order')->insert($order, false, true);

        if (isset($data['goodss'])) {
            foreach ($data['goodss'] as $goods) {

                $goods_id = $goods['goods_id'];

                $order_goods_id = Db::name('order_goods')->insert(array(
                    'order_id' => $order_id,
                    'goods_id' => $goods_id,
                    'name'     => $goods['name'],
                    'model'    => $goods['model'],
                    'quantity' => (int)$goods['quantity'],
                    'price'    => (float)$goods['price'],
                    'total'    => (float)$goods['total']
                ), false, true);

                foreach ($goods['option'] as $option) {
                    Db::execute("INSERT INTO " . config('database.prefix') . "order_option SET order_id = '" . $order_id
                        . "',order_goods_id='" . $order_goods_id . "'"
                        . ",goods_id='" . (int)$option['goods_id'] . "'"
                        . ",option_id='" . (int)$option['option_id'] . "'"
                        . ",option_value_id='" . (int)$option['option_value_id'] . "'"
                        . ",name='" . $option['name'] . "'"
                        . ",value='" . $option['value'] . "'"
                        . ",type='" . $option['type'] . "'"
                    );
                }
                //支付成功后扣除库存

            }
        }

        return [
            'order_id'     => $order_id,
            'subject'      => $order['pay_subject'],
            'name'         => $order['shipping_name'],//收货人姓名
            'pay_order_no' => $order['order_num_alias'],
            'pay_total'    => $order['total'],
            'uid'          => $order['uid']
        ];

    }

    private function get_order_data($param = array())
    {

        if (empty($param)) {
            $shipping_address_id = (int)session('shipping_address_id');//送货地址
            $shipping_method     = session('shipping_method');//送货方式
            $payment_method      = session('payment_method');//支付方式
            $weight              = (float)session('weight');//重量
            $shipping_city_id    = (int)session('shipping_city_id');//配送的城市，到市级地址
            $comment             = session('comment');//留言
            $uid                 = (int)member('uid');
        } else {
            $shipping_address_id = (int)$param['shipping_address_id'];
            $shipping_method     = $param['shipping_method'];
            $payment_method      = $param['payment_method'];
            $weight              = (float)$param['weight'];
            $shipping_city_id    = (int)$param['shipping_city_id'];
            $comment             = $param['comment'];
            $uid                 = (int)$param['uid'];
        }

        if (isset($param['type'])) {
            $type             = $param['type'];
            $data['pay_type'] = $param['type'];
        } else {
            $type = 'money';
        }

        $goodss = osc_cart()->get_all_goods($uid, $type);
        //付款人
        $payment = Db::name('member')->find($uid);
        //收货人
        $shipping = Db::name('address')->find($shipping_address_id);

        $data['uid']   = $payment['uid'];
        $data['name']  = $payment['username'];
        $data['email'] = $payment['email'];
        $data['tel']   = $payment['telephone'];

        //此处为了支持免配送商品
        $data['shipping_name']        = empty($shipping['name']) ? '' : $shipping['name'];
        $data['shipping_tel']         = empty($shipping['telephone']) ? '' : $shipping['telephone'];
        $data['shipping_province_id'] = empty($shipping['province_id']) ? '' : $shipping['province_id'];
        $data['shipping_city_id']     = empty($shipping['city_id']) ? '' : $shipping['city_id'];
        $data['shipping_country_id']  = empty($shipping['country_id']) ? '' : $shipping['country_id'];
        $data['address_id']           = empty($shipping_address_id) ? '' : $shipping_address_id;
        $data['shipping_method']      = empty($shipping_method) ? '' : $shipping_method;

        $data['payment_method'] = $payment_method;

        $data['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
        $data['date_added'] = time();

        $data['comment'] = empty($comment) ? '' : $comment;

        $subject = '';

        if ($goodss) {
            //运费
            $transport_fee = osc_transport()->calc_transport($shipping_method, $weight, $shipping_city_id);

            $t             = 0;
            $pay_points    = 0;
            $return_points = 0;
            foreach ($goodss as $goods) {

                $option_data = array();

                foreach ($goods['option'] as $option) {

                    $value = $option['value'];

                    $option_data[] = array(
                        'goods_id'        => $goods['goods_id'],
                        'option_id'       => $option['option_id'],
                        'option_value_id' => $option['option_value_id'],
                        'name'            => $option['name'],
                        'value'           => $value,
                        'type'            => $option['type']
                    );
                }

                $t             += $goods['total'];
                $pay_points    += $goods['total_pay_points'];
                $return_points += $goods['total_return_points'];

                $goods_data[] = array(
                    'goods_id' => $goods['goods_id'],
                    'name'     => $goods['name'],
                    'model'    => $goods['model'],
                    'option'   => $option_data,
                    'quantity' => $goods['quantity'],
                    'subtract' => $goods['subtract'],
                    'price'    => $goods['price'],
                    'total'    => $goods['total']
                );

            }
            if (count($goodss) > 1) {
                $subject = $goodss[0]['name'] . '等商品';
            } else {
                $subject = $goodss[0]['name'];
            }
            $data['pay_subject'] = $subject;

            if ($type == 'points') {//积分兑换的
                $data['total'] = $pay_points;

                $data['totals'][0] = array(
                    'code'  => 'sub_total',
                    'title' => '商品价格',
                    'text'  => '￥ 0',
                    'value' => 0
                );
                $data['totals'][1] = array(
                    'code'  => 'shipping',
                    'title' => '运费',
                    'text'  => '￥ 0',
                    'value' => 0
                );
                $data['totals'][2] = array(
                    'code'  => 'total',
                    'title' => '总价',
                    'text'  => '￥ 0',
                    'value' => 0
                );

            } elseif ($type == 'money') {//在线支付的
                $data['total']         = ($t + $transport_fee['price']);
                $data['return_points'] = $return_points;//可得积分
                $data['totals'][0]     = array(
                    'code'  => 'sub_total',
                    'title' => '商品价格',
                    'text'  => '￥' . $t,
                    'value' => $t
                );
                $data['totals'][1]     = array(
                    'code'  => 'shipping',
                    'title' => '运费',
                    'text'  => '￥' . $transport_fee['price'],
                    'value' => $transport_fee['price']
                );
                $data['totals'][2]     = array(
                    'code'  => 'total',
                    'title' => '总价',
                    'text'  => '￥' . ($t + $transport_fee['price']),
                    'value' => ($t + $transport_fee['price'])
                );

            }

            $data['goodss']          = $goods_data;
            $data['order_num_alias'] = build_order_no();


            return $data;
        }
    }

    //更新订单，商品数量
    public function update_order($order_id)
    {

        $order_info = Db::name('order')->where('order_id', $order_id)->find();

        $order['order_id']        = $order_id;
        $order['order_status_id'] = config('paid_order_status_id');
        $order['date_modified']   = time();
        $order['pay_time']        = time();
        Db::name('order')->update($order);


        $member = Db::name('member')->where('uid', $order_info['uid'])->find();

        $list = Db::name('goods')
            ->alias('g')
            ->join('__ORDER_GOODS__ og', 'g.goods_id = og.goods_id', 'left')
            ->join('__ORDER_OPTION__ oo', 'og.order_goods_id = oo.order_goods_id', 'left')
            ->field('oo.*,g.*,og.quantity as goods_quantity')
            ->where('og.order_id', $order_id)
            ->select();

        //更新商品数量
        foreach ($list as $k => $v) {
            //存在选项
            if ($v['order_option_id']) {
                if ($v['subtract']) {//需要扣减库存

                    $map['goods_id']        = ['eq', $v['goods_id']];
                    $map['option_id']       = ['eq', $v['option_id']];
                    $map['option_value_id'] = ['eq', $v['option_value_id']];

                    Db::name('goods_option_value')->where($map)->setDec('quantity', $v['goods_quantity']);
                    Db::name('goods')->where('goods_id', $v['goods_id'])->setDec('quantity', $v['goods_quantity']);
                }
                //不存在选项
            } else {
                //需要扣减库存
                if ($v['subtract'])
                    Db::name('goods')->where('goods_id', $v['goods_id'])->setDec('quantity', $v['goods_quantity']);
            }
        }
        storage_user_action($order_info['uid'], $order_info['name'], config('FRONTEND_USER'), '成功支付了订单');

    }

    //会员中心点击立即支付，验证商品数量
    public function check_goods_quantity($order_id)
    {
        $goods_list = Db::view('OrderGoods', 'name,quantity as order_quantity')
            ->view('Goods', 'quantity as goods_quantity', 'Goods.goods_id=OrderGoods.goods_id')
            ->where('order_id', $order_id)->select();

        foreach ($goods_list as $k => $v) {
            if ($v['order_quantity'] > $v['goods_quantity']) {
                return ['error' => $v['name'] . ',数量不足，剩余' . $v['goods_quantity']];
            }
        }

    }

    /**
     * 导出订单
     * @param int $history 是否导出历史订单 （默认0导出7天内订单，1导出历史订单）
     * @throws \PHPExcel_Exception
     * @throws \PHPExcel_Reader_Exception
     * @throws \PHPExcel_Writer_Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function toExport($history = 0)
    {
        $name = '会员订单表';

        if ($history == 1) {
            //显示历史订单，大于等于7天
            $map['Order.order_status'] = ['in',[3,4]];
            $map['Order.create_time'] = ['lt', time()-(7*24*3600)];
            $page = Db::view('Order', '*')
                ->view('Dispatch', 'dispatch_title', 'Order.dispatch_id=Dispatch.id')
                ->where($map)
                ->order('Order.shipping_name,Order.create_time desc')
                ->select();
        }else{
            //默认会员订单，7天内
            $map['Order.order_status'] = ['in', [1,2]];
            $page = Db::name('Order')->alias('Order')->field('*,d.dispatch_title')
                ->join('__DISPATCH__ d', 'Order.dispatch_id=d.id')
                ->where($map)
                ->union(function($query){
                    $map2['Order.order_status'] = ['in', [3,4]];
                    $map2['Order.create_time'] = ['egt', time()-(7*24*3600)];

                    $query->table('__ORDER__')->alias('Order')->field('*,d.dispatch_title')
                        ->join('__DISPATCH__ d', 'Order.dispatch_id=d.id')
                        ->where($map2)
                        ->order('shipping_name,create_time desc');
                })
                ->select();
        }

        if (count($page) > 0) {
            foreach ($page as $key => $v) {
                $page[$key]['order_status'] = getOrderStatus($v['order_status']);
                $page[$key]['create_time']  = date('Y-m-d H:i:s', $v['create_time']);
            }
        }

        Loader::import('phpexcel.PHPExcel.IOFactory');
        $objPHPExcel = new \PHPExcel();

        // 设置excel文档的属性
        $objPHPExcel->getProperties()->setCreator("Admin")//创建人
        ->setLastModifiedBy("Admin")//最后修改人
        ->setTitle($name)//标题
        ->setSubject($name)//题目
        ->setDescription($name)//描述
        ->setKeywords("订单")//关键字
        ->setCategory("Test result file");//种类

        // 开始操作excel表
        $objPHPExcel->setActiveSheetIndex(0);
        // 设置工作薄名称
        $objPHPExcel->getActiveSheet()->setTitle(iconv('gbk', 'utf-8', 'Sheet'));
        // 设置默认字体和大小
        $objPHPExcel->getDefaultStyle()->getFont()->setName(iconv('gbk', 'utf-8', ''));
        $objPHPExcel->getDefaultStyle()->getFont()->setSize(11);
        $styleArray = array(
            'font'    => array(
                'bold'  => true,
                'color' => array(
                    'argb' => 'ffffffff',
                )
            ),
            'borders' => array(
                'outline' => array(
                    'style' => \PHPExcel_Style_Border::BORDER_THIN,  //设置border样式
                    'color' => array('argb' => 'FF000000'),     //设置border颜色
                )
            )
        );
        //设置宽
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(8);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(18);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(50);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID);
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->getFill()->getStartColor()->setARGB('333399');

        $objPHPExcel->getActiveSheet()->setCellValue('A1', '订单ID')->setCellValue('B1', '订单号')->setCellValue('C1', '收货人')->setCellValue('D1', '联系电话')
            ->setCellValue('E1', '收货地址')->setCellValue('F1', '下单时间')->setCellValue('G1', '主账户消费')->setCellValue('H1', '小金库消费')->setCellValue('I1', '总计')->setCellValue('J1', '状态');
        $objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray($styleArray);

        for ($row = 0; $row < count($page); $row++) {
            $i = $row + 2;
            $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $page[$row]['order_id'])->setCellValue('B' . $i, $page[$row]['order_num_alias'])->setCellValue('C' . $i, $page[$row]['shipping_name'])
                ->setCellValue('D' . $i, $page[$row]['shipping_tel'])->setCellValue('E' . $i, $page[$row]['shipping_addr'])->setCellValue('F' . $i, $page[$row]['create_time'])->setCellValue('G' . $i, $page[$row]['mainPay'])->setCellValue('H' . $i, $page[$row]['secondPay'])
                ->setCellValue('I' . $i, $page[$row]['total'])->setCellValue('J' . $i, $page[$row]['order_status']);
        }

        //输出EXCEL格式
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        // 从浏览器直接输出$filename
        header('Content-Type:application/csv;charset=UTF-8');
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-excel;");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition: attachment;filename="' . $name . '.xls"');
        header("Content-Transfer-Encoding:binary");
        $objWriter->save('php://output');
    }

}

?>