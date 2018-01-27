import { Category } from 'category-model.js';
var category=new Category();  //实例化 home 的推荐页面
Page({
  data: {
    currentMenuId:0,
    currentMenuIndex: 0,
    loadingHidden:false,
    procucts:[],
  },
  onLoad: function () {
    this._loadData();
  },

  /*加载所有数据*/
  _loadData:function(callback){
    var that = this;

    /*获取分类信息*/
    category.getCategoryType((categoryData)=>{
      console.log(categoryData)
      that.setData({
        categoryTypeArr: categoryData,
        currentMenuId: categoryData[0].id,
        title: categoryData[0].name,
        imgUrl: categoryData[0].image,
        loadingHidden: true,
      });
  
      /*获取第一个分类下对应的商品*/
      this.getProductsByCategory(categoryData[0].id);

    });
  },

  /*切换分类*/
  changeCategory:function(event){
    var id=category.getDataSet(event,'id'),
      index = category.getDataSet(event, 'index'),
      name = category.getDataSet(event, 'name'),
      image = category.getDataSet(event, 'image');

    this.data.procucts = [];
    this.setData({
      currentMenuId: id,
      currentMenuIndex: index,
      loadingHidden: true,
      title: name,
      imgUrl: image,
      procucts: this.data.procucts
    });
    this.getProductsByCategory(id);
  },

  /*获取分类下对应的商品*/
  getProductsByCategory: function (id){
    var that = this;
    category.getProductsByCategory(id,(data)=> {
      that.setData({
        procucts: data
      });
    });
  },

  /*跳转到商品详情*/
  onProductsItemTap: function (event) {
    var id = category.getDataSet(event, 'id');
    wx.navigateTo({
      url: '../product/product?id=' + id
    })
  },

  /*下拉刷新页面*/
  onPullDownRefresh: function(){
    this._loadData(()=>{
      wx.stopPullDownRefresh()
    });
  },

  //分享效果
  onShareAppMessage: function () {
    return {
      title: '悦蔻霖智',
      path: 'pages/category/category'
    }
  }

})