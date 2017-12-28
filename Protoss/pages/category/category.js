import { Category } from 'category-model.js';
var category=new Category();  //实例化 home 的推荐页面
Page({
  data: {
    currentMenuIndex:0,
    loadingHidden:false,
    categoryInfo:[],
  },
  onLoad: function () {
    this._loadData();
  },

  /*加载所有数据*/
  _loadData:function(callback){
    var that = this;

    /*获取分类信息*/
    category.getCategoryType((categoryData)=>{

      that.setData({
        categoryTypeArr: categoryData
      });
      
      /*缓存中是否有该商品*/
      var isHadInfo = category._isHasThatOne(categoryData[0].id);
      if (isHadInfo.index != -1) {
        this.setData({
          loadingHidden: true,
          categoryInfo: isHadInfo.data
        });
      }
      else {
        /*获取第一个分类下对应的商品*/
        this.getProductsByCategory(categoryData[0].id, 0);
      }

    });
  },

  /*切换分类*/
  changeCategory:function(event){
    var index=category.getDataSet(event,'index'),
        id=category.getDataSet(event,'id');

    this.setData({
      loadingHidden: false,
      currentMenuIndex: index,
    });

    /*缓存中是否有该商品*/
    var isHadInfo = category._isHasThatOne(id);
    if (isHadInfo.index != -1) {
      this.setData({
        loadingHidden: true,
        categoryInfo: isHadInfo.data
      });
    }
    else
    {
      /*获取分类下对应的商品*/
      this.getProductsByCategory(id,index);
    }
  },

  /*获取分类下对应的商品*/
  getProductsByCategory: function (id, index){
    var that = this;
    category.getProductsByCategory(id,(data)=> {
      var baseData = this.data.categoryTypeArr[index];
      var dataObj = {
        procucts: data,
        topImgUrl: baseData.image,
        title: baseData.name,
        id: baseData.id
      };
      that.setData({
        loadingHidden: true,
        categoryInfo: dataObj
      });
      category.addCategory(dataObj);
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
      title: '悦寇霖智',
      path: 'pages/category/category'
    }
  }

})