<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:49:"./oscshop/mobile\view\search\ajax_goods_list.html";i:1512525352;}*/ ?>
<?php if(!empty($goods)): ?>
<div class="clearfix pdBlock">
    <?php if(is_array($goods) || $goods instanceof \think\Collection || $goods instanceof \think\Paginator): $i = 0; $__LIST__ = $goods;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$data): $mod = ($i % 2 );++$i;?>
        <section class="productListWrap hoz" onclick="location = '<?php echo url("goods/detail",array("id"=>$data["goods_id"])); ?>';">
            <a class="productList">
                <img src="IMG_ROOT<?php echo $data['image']; ?>" />           
                <section>
                    <title class="title"><?php echo $data['name']; ?></title>
                    <span class='prices'>&yen;<?php echo $data['price']; ?></span>
                </section>
            </a>
        </section>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</div>
<?php endif; ?>