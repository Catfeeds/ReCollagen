<?php

namespace app\api\controller\v1;

use app\api\model\Product as ProductModel;
use app\api\model\UserCollect as UserCollectModel;
use app\api\service\Token as TokenService;

use app\api\validate\Count;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\PagingParameter;
use app\lib\exception\ParameterException;
use app\lib\exception\ProductException;
use app\lib\exception\ThemeException;
use app\lib\exception\SuccessMessage;
use think\Controller;
use think\Exception;

class Product extends Controller
{
    protected $beforeActionList = [
        'checkSuperScope' => ['only' => 'createOne,deleteOne']
    ];
    
    /**
     * 获取该类目下所有商品(分页）
     * @url /product?id=:category_id&page=:page&size=:page_size
     * @param int $id 商品id
     * @param int $page 分页页数（可选)
     * @param int $size 每页数目(可选)
     * @return array of Product
     * @throws ParameterException
     */
    public function getByCategory($id = -1, $page = 1, $size = 30)
    {
        (new IDMustBePositiveInt())->goCheck();
        (new PagingParameter())->goCheck();
        $pagingProducts = ProductModel::getProductsByCatId(
            $id, true, $page, $size);
        if ($pagingProducts->isEmpty())
        {
            // 对于分页最好不要抛出MissException，客户端并不好处理
            return [
                'current_page' => $pagingProducts->currentPage(),
                'data' => []
            ];
        }
        //数据集对象和普通的二维数组在使用上的一个最大的区别就是数据是否为空的判断，
        //二维数组的数据集判断数据为空直接使用empty
        //collection的判空使用 $collection->isEmpty()

        // 控制器很重的一个作用是修剪返回到客户端的结果

        //        $t = collection($products);
        //        $cutProducts = collection($products)
        //            ->visible(['id', 'name', 'img'])
        //            ->toArray();

//        $collection = collection($pagingProducts->items());
        $data = $pagingProducts
            ->hidden(['quantity','weight','bulk'])
            ->toArray();
        // 如果是简洁分页模式，直接序列化$pagingProducts这个Paginator对象会报错
        //        $pagingProducts->data = $data;            
        return [
            'current_page' => $pagingProducts->currentPage(),
            'data' => $data
        ];
    }

    /**
     * 获取该分类下所有商品(不分页）
     * @url /product/all/:category_id
     * @param int $id 分类id号
     * @return \think\Paginator
     * @throws ThemeException
     */
    public function getProductsByCatId($id = -1){

        (new IDMustBePositiveInt())->goCheck();
        $products = ProductModel::getProductsByCatId($id, false);
        if ($products->isEmpty()){
            throw new ThemeException();
        }
        $data = $products
            ->hidden(['stock','weight','bulk'])
            ->toArray();

        return $data;
    }

    /**
     * 获取最近商品
     * @url /product/recent
     * @return mixed
     * @throws ParameterException
     */
    public function getRecent(){

        (new Count())->goCheck();
        $pagingProducts = ProductModel::getMostRecent();

        if ($pagingProducts->isEmpty()){
            return [];
        }
        $data = $pagingProducts->hidden(
            [
                'cat_id','weight','bulk','stock'
            ])
            ->toArray();

        return $data;

    }

    /**
     * 获取商品详情
     * 如果商品详情信息很多，需要考虑分多个接口分布加载
     * @url /product/:id
     * @param int $id 商品id号
     * @return Product
     * @throws ProductException
     */
    public function getOne($id){
        
        (new IDMustBePositiveInt())->goCheck();
        $product = ProductModel::getProductDetail($id);

        if (!$product){
            throw new ProductException();
        }
        // //判断用户是否已收藏
        // $currentUid  = TokenService::getCurrentUid();
        // $where       = ['uid'=>$currentUid,'goods_id'=>$id];
        // $haveCollect = $this->haveCollectGoods($where);
        // $product['haveCollect'] = $haveCollect ? 1:0;

        return $product;
    }

    public function createOne()
    {
        $product = new ProductModel();
        $product->save(
            [
                'id' => 1
            ]);
    }

    public function deleteOne($id)
    {
        ProductModel::destroy($id);
        //        ProductModel::destroy(1,true);
    }
    /**
     * 收藏或取消收藏商品
     */
    public function collectGoods(){
        
        (new IDMustBePositiveInt())->goCheck();

        $id = input('post.id/d');

        $UserCollectModel = new UserCollectModel();

        $currentUid = TokenService::getCurrentUid();

        $where = ['uid'=>$currentUid,'goods_id'=>$id];

        $haveCollect = $this->haveCollectGoods($where);
        if (!$haveCollect) {
            $UserCollectModel->save($where);
        }else{
            UserCollectModel::destroy($where);
        }
        return new SuccessMessage();

    }
    /**
     * 判断用户是否已收藏该商品
     */
    private function haveCollectGoods($where){

        $UserCollectModel = new UserCollectModel();

        return $UserCollectModel->where($where)->find();

    }
    /**
     * 获取已收藏的商品列表
     */
    public function getcollectGoodsList(){

        $currentUid = TokenService::getCurrentUid();
        $where = ['uid'=>$currentUid];

        $collect = UserCollectModel::where($where)->order('create_time desc')->select();
        if ($collect->isEmpty()){
            return [];
        }
        $goodsIds = array_column($collect->toArray(),'goods_id');

       
        $list = ProductModel::getcollectGoodsList($goodsIds);
        $list = $list->hidden(
            [
                'cat_id','stock','weight','bulk'
            ])
            ->toArray();

        return $list;

    }

}