<?php if (!defined('THINK_PATH')) exit(); /*a:4:{s:39:"./oscshop/mobile\view\search\index.html";i:1512552775;s:38:"./oscshop/mobile\view\public\base.html";i:1512552775;s:40:"./oscshop/mobile\view\public\search.html";i:1512552775;s:44:"./oscshop/mobile\view\public\footer-nav.html";i:1512552775;}*/ ?>
<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv=Content-Type content="text/html;charset=utf-8" />
    <title><?php echo (isset($SEO['title']) && ($SEO['title'] !== '')?$SEO['title']:''); ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=0" />
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="format-detection" content="telephone=no">    
      
    
<link href="__PUBLIC__/view_res/mobile/css/productlist.css" type="text/css" rel="Stylesheet" />
    
</head>
<body>

<form class="search-w-box" onsubmit='searchdo(this);return false;'>
	<input type="search" name="search" id="searchBox" targ="<?php echo url('search/index'); ?>" value="<?php echo input('param.search'); ?>"  class="search-w-input" placeholder="搜一搜，找到你想要的" /> 
</form>
<script>
function searchdo(form) {
    var search = $('input[type=search]', form);
    if (search.val() === '')
    return;            
 	var url = search.attr('targ');
 	
    var query = $('.search-w-box').find('input,select').serialize();
    query = query.replace(/(&|^)(\w*?\d*?\-*?_*?)*?=?((?=&)|(?=$))/g, '');
    query = query.replace(/^&/g, '');
    if (url.indexOf('?') > 0) {
        url += '&' + query;
    } else {
        url += '?' + query;
    }
    window.location.href = url;            
}	
</script>



<input type="hidden" value="<?php echo input('param.search'); ?>" id="searchkey" />
<input type="hidden" value="goods_id" id="orderby" />

<div class="subnav_wra">
    <section class="subnavBox clearfix">
        <div class="subnav" orderby="goods_id"><span>最新</span></div>
        <div class="subnav" orderby="price"><span>价格<b class="up _priceB"></b></span></div>
        <div class="subnav" orderby="sale_count"><span>销量</span></div>
        <div class="subnav" orderby="viewed"><span>人气</span></div>
    </section>
    <div id="plistDp"></div>
</div>

<div id="product_list" class="clearfix"></div>

<div id="list-loading" style="display: none;"><img src="__PUBLIC__/view_res/mobile/images/icon/spinner-g-60.png" width="30"></div>


<footer id="footer">
<div class="bottom_nav"><a class="nav_index <?php if(isset($flag) AND ($flag == 'index')): ?> hover<?php endif; ?>" href="<?php echo url('/mobile'); ?>"><i></i>购物</a>
<a class="nav_search <?php if(isset($flag) AND ($flag == 'search')): ?> hover<?php endif; ?>" href="<?php echo url('category/index'); ?>"><i></i>搜索</a>	
<a class="nav_shopcart" href="<?php echo url('cart/index'); ?>"><i></i>购物车</a>	
<a class="nav_me <?php if(isset($flag) AND ($flag == 'user')): ?> hover<?php endif; ?>" href="<?php echo url('user/index'); ?>"><i></i>个人中心</a></div>		
</footer>

</body>

<script>
var _hmt = _hmt || [];
(function() {
  var hm = document.createElement("script");
  hm.src = "//hm.baidu.com/hm.js?32e3cb4cf7e5c6772c4dc1f5d496b6af";
  var s = document.getElementsByTagName("script")[0]; 
  s.parentNode.insertBefore(hm, s);
})();
</script>



<script data-main="__RES__/mobile/js/search.js" src="__RES__/mobile/js/require.min.js"></script>

</html>