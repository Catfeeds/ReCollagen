<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:46:"./oscshop/member\view\order_backend\order.html";i:1512552775;}*/ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>下单成功-<?php echo config('SITE_NAME'); ?></title>
</head>
<?php use think\Db; ?>
<body style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; color: #000000;">
<div style="width: 680px;"><h2><a style="text-decoration:none" href="<?php echo request()->domain(); ?>" title="<?php echo config('SITE_NAME'); ?>"><?php echo config('SITE_NAME'); ?></a></h2>
  
  <?php if(isset($seller_comment)){ ?>
  <p style="color:#000;font-size:20px;font-weight:bold;"><?php echo $seller_comment; ?></p>
  <?php } ?>
  
  <p style="margin-top: 0px; margin-bottom: 20px;">感谢您对<?php echo config('SITE_NAME'); ?>产品的支持</p>
 

  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
      <tr>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;" colspan="2">订单信息</td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b>订单号</b> <?php echo $order['order']['order_num_alias']; ?><br />
          <b>付款时间</b> <?php if($order['order']['pay_time']){echo date('Y-m-d H:i:s',$order['order']['pay_time']);}else{echo '无';} ?><br />
          <b>支付方式</b> 
          <?php switch($order['order']['payment_code']): case "alipay": ?>支付宝<?php break; case "weixin": ?>微信支付<?php break; endswitch; ?>
				<br />
          <?php if ($order['order']['shipping_method']) { ?>
          <b>货运方式</b> <?php echo get_shipping_name($order['order']['shipping_method']); } ?></td>
          <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><b>邮箱</b> <?php echo $order['order']['email']; ?><br />
          <b>收货人联系号码</b><?php echo $order['order']['shipping_tel']; ?><br />
          <b>收货人</b> <?php echo $order['order']['shipping_name']; ?><br />
          <b>订单状态</b> <?php echo $order['order']['name']; ?><br /></td>
      </tr>
    </tbody>
  </table>
  <?php if (!empty($order['order']['comment'])) { ?>
  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
      <tr>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">订单留言</td>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $order['order']['comment']; ?></td>
      </tr>
    </tbody>
  </table>
  <?php } ?>
  
  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
      <tr>
        
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">收货地址</td>
        
      </tr>
    </thead>
    <tbody>
      <tr>
       
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
        		<?php echo get_area_name($order['order']['shipping_province_id']).','.get_area_name($order['order']['shipping_city_id']).','.get_area_name($order['order']['shipping_country_id']).','.$order['order']['address']; ?>
        </td>
        
      </tr>
    </tbody>
  </table>
  <table style="border-collapse: collapse; width: 100%; border-top: 1px solid #DDDDDD; border-left: 1px solid #DDDDDD; margin-bottom: 20px;">
    <thead>
      <tr>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">产品名称</td>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: left; padding: 7px; color: #222222;">型号</td>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">数量</td>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">单价</td>
        <td style="font-size: 12px; border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; background-color: #EFEFEF; font-weight: bold; text-align: right; padding: 7px; color: #222222;">总计</td>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($order['order_product'] as $product) { ?>
      <tr>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;">
        	<?php echo $product['name']; if($option_list=Db::query('select * from '.config('database.prefix').'order_option where order_goods_id='.$product['order_goods_id'].' and order_id='.$product['order_id'])){ foreach ($option_list as $option) { ?>
                <br />
                &nbsp;<small> - <?php echo $option['name']; ?>: <?php echo $option['value']; ?></small>
                <?php } } ?>
        </td>
       
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: left; padding: 7px;"><?php echo $product['model']; ?></td>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $product['quantity']; ?></td>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo '￥'.$product['price']; ?></td>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo '￥'.$product['total']; ?></td>
      </tr>
      <?php } ?>
   
    </tbody>
    <tfoot>
    	<?php if(is_array($order['order_total']) || $order['order_total'] instanceof \think\Collection || $order['order_total'] instanceof \think\Paginator): $i = 0; $__LIST__ = $order['order_total'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$total): $mod = ($i % 2 );++$i;?>
      <tr>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;" colspan="4"><b><?php echo $total['title']; ?>:</b></td>
        <td style="font-size: 12px;	border-right: 1px solid #DDDDDD; border-bottom: 1px solid #DDDDDD; text-align: right; padding: 7px;"><?php echo $total['text']; ?></td>
      </tr>
     <?php endforeach; endif; else: echo "" ;endif; ?>
    </tfoot>
  </table>

  <p>您可以登录网站查看订单</p>
  <p style="margin-top: 0px; margin-bottom: 20px;">感谢您对<?php echo config('SITE_NAME'); ?>产品的支持，欢迎下次光顾</p>

</div>
</body>
</html>
