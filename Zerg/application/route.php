<?php
/**
 * 路由注册
 *
 * 以下代码为了尽量简单，没有使用路由分组
 * 实际上，使用路由分组可以简化定义
 * 并在一定程度上提高路由匹配的效率
 */

// 写完代码后对着路由表看，能否不看注释就知道这个接口的意义
use think\Route;

//Sample
Route::get('api/:version/sample/:key', 'api/:version.Sample/getSample');
Route::post('api/:version/sample/test3', 'api/:version.Sample/test3');

//Miss 404
//Miss 路由开启后，默认的普通模式也将无法访问
//Route::miss('api/v1.Miss/miss');

//Banner
Route::get('api/:version/banner', 'api/:version.Banner/getBanners');

//Theme
// 如果要使用分组路由，建议使用闭包的方式，数组的方式不允许有同名的key
//Route::group('api/:version/theme',[
//    '' => ['api/:version.Theme/getThemes'],
//    ':t_id/product/:p_id' => ['api/:version.Theme/addThemeProduct'],
//    ':t_id/product/:p_id' => ['api/:version.Theme/addThemeProduct']
//]);

Route::group('api/:version/theme',function(){
    Route::get('', 'api/:version.Theme/getSimpleList');
    Route::get('/:id', 'api/:version.Theme/getComplexOne');
    Route::post(':t_id/product/:p_id', 'api/:version.Theme/addThemeProduct');
    Route::delete(':t_id/product/:p_id', 'api/:version.Theme/deleteThemeProduct');
});

//Route::get('api/:version/theme', 'api/:version.Theme/getThemes');
//Route::post('api/:version/theme/:t_id/product/:p_id', 'api/:version.Theme/addThemeProduct');
//Route::delete('api/:version/theme/:t_id/product/:p_id', 'api/:version.Theme/deleteThemeProduct');

//User
Route::get('api/:version/account','api/:version.User/getUserAccount');          //获取用户账户信息
Route::post('api/:version/user/update','api/:version.User/editUserData');        //修改用户信息

//Product
Route::post('api/:version/product', 'api/:version.Product/createOne');
Route::delete('api/:version/product/:id', 'api/:version.Product/deleteOne');
Route::get('api/:version/product/by_category/paginate', 'api/:version.Product/getByCategory');  //获取该类目下所有商品(分页）
Route::get('api/:version/product/by_category/:id', 'api/:version.Product/getProductsByCatId',[], ['id'=>'\d+']);        //获取该类目下所有商品(不分页）
Route::get('api/:version/product/:id', 'api/:version.Product/getOne',[],['id'=>'\d+']);         //获取商品详情
Route::get('api/:version/product/recent', 'api/:version.Product/getRecent');                    //首页最新新品列表
Route::post('api/:version/product/collect','api/:version.Product/collectGoods');                //收藏或取消收藏商品
Route::get('api/:version/product/collect','api/:version.Product/getcollectGoodsList');          //获取已收藏的商品列表


//Promotion
Route::get('api/:version/promotion/all', 'api/:version.Promotion/getPromotions');                    //正在进行中的促销活动
Route::get('api/:version/promotion/info', 'api/:version.Promotion/getPromotionInfo');                //促销信息
Route::get('api/:version/promotion/img', 'api/:version.Promotion/getPromotionImg');                  //促销图片

//Cart
Route::post('api/:version/cart/add','api/:version.Cart/addCart');                //添加到购物车
Route::post('api/:version/cart/del','api/:version.Cart/delCart');                //从购物车删除商品
Route::post('api/:version/cart/setByClick','api/:version.Cart/setCountByClick'); //点击增加或减少购物车商品数量
Route::post('api/:version/cart/setByInput','api/:version.Cart/setCountByInput'); //输入数字修改购物车商品数量
Route::post('api/:version/cart/setChecked','api/:version.Cart/setChecked');      //设置购物车商品选中状态
Route::post('api/:version/cart/batchSetChecked','api/:version.Cart/batchSetChecked'); //批量设置购物车商品选中状态
Route::get('api/:version/cart/goods','api/:version.Cart/getCartGoods');          //获取购物车商品
Route::get('api/:version/cart/preOrder','api/:version.Cart/getPreOrderDetail');   //获取预下单详情清单


//Category
Route::get('api/:version/category', 'api/:version.Category/getCategories'); 
// 正则匹配区别id和all，注意d后面的+号，没有+号将只能匹配个位数
//Route::get('api/:version/category/:id', 'api/:version.Category/getCategory',[], ['id'=>'\d+']);
//Route::get('api/:version/category/:id/products', 'api/:version.Category/getCategory',[], ['id'=>'\d+']);
Route::get('api/:version/category/all', 'api/:version.Category/getAllCategories');      //获取所有商品分类

//Token
Route::post('api/:version/token/user', 'api/:version.Token/getToken');
Route::post('api/:version/token/app', 'api/:version.Token/getAppToken');
Route::post('api/:version/token/verify', 'api/:version.Token/verifyToken');

//Address
Route::post('api/:version/address/create', 'api/:version.Address/createAddress');
Route::post('api/:version/address/update', 'api/:version.Address/updateAddress');
Route::post('api/:version/address/del', 'api/:version.Address/delAddress');
Route::get('api/:version/address', 'api/:version.Address/getUserAddress');

//Order
Route::post('api/:version/order', 'api/:version.Order/createOrder');                    //创建订单
Route::post('api/:version/order/cancel', 'api/:version.Order/cancelOrder');             //取消订单
Route::post('api/:version/order/receive', 'api/:version.Order/receiveOrder');           //确认收货
Route::get('api/:version/order/:id', 'api/:version.Order/getDetail',[], ['id'=>'\d+']); //获取订单详情
Route::get('api/:version/order/transinfo/:id', 'api/:version.Order/getTransInfo',[], ['id'=>'\d+']); //根据订单id查询物流进度
Route::post('api/:version/order/transfee', 'api/:version.Order/getTransFee');           //根据商品重量匹配物流公司和运费


Route::put('api/:version/order/delivery', 'api/:version.Order/delivery');
//不想把所有查询都写在一起，所以增加by_user，很好的REST与RESTFul的区别
Route::get('api/:version/order/by_user/:status/:page', 'api/:version.Order/getSummaryByUser');        //根据订单类型获取某个用户订单列表
Route::get('api/:version/order/paginate', 'api/:version.Order/getSummary');

//Pay
Route::post('api/:version/pay/pre_order', 'api/:version.Pay/getPreOrder');
Route::post('api/:version/pay/notify', 'api/:version.Pay/receiveNotify');
Route::post('api/:version/pay/re_notify', 'api/:version.Pay/redirectNotify');
Route::post('api/:version/pay/concurrency', 'api/:version.Pay/notifyConcurrency');

//Message
Route::post('api/:version/message/delivery', 'api/:version.Message/sendDeliveryMsg');



//return [
//        ':version/banner/[:location]' => 'api/:version.Banner/getBanner'
//];

//Route::miss(function () {
//    return [
//        'msg' => 'your required resource are not found',
//        'error_code' => 10001
//    ];
//});



