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
    category.getCategoryType((categoryData)=>{
      that.setData({
        categoryTypeArr: categoryData,
        loadingHidden: true
      });
      that.getProductsByCategory(categoryData[0].id,(data)=>{
        var dataObj= [{
          procucts: data,
          id: categoryData[0].id,
          topImgUrl: categoryData[0].img.url,
          title: categoryData[0].name
        }];
        that.setData({
          loadingHidden: true,
          categoryInfo: dataObj
        });
        callback&& callback();
      });
    });
  },

  /*切换分类*/
  changeCategory:function(event){
    var index=category.getDataSet(event,'index'),
        id=category.getDataSet(event,'id')//获取data-set
    this.setData({
      currentMenuIndex: index
    });

    //如果数据是第一次请求
    if (!this.isLoadedData(id)) {
      var that=this;
      this.getProductsByCategory(id, (data)=> {
        var baseData = that.data.categoryTypeArr[index];
        var dataObj = {
          procucts: data,
          id: baseData.id,
          topImgUrl: baseData.img.url,
          title: baseData.name
        };
        var prData = that.data.categoryInfo;
        var classData = prData.concat(dataObj);
        that.setData({
          loadingHidden: true,
          categoryInfo: classData
        });
      });
    }
  },

  isLoadedData: function (id){
    
    var prData = this.data.categoryInfo;
    var i = prData.length;
    while (i--) {
      if (prData[i].id === id) {
        return true
      }
    }
    return false
  },
  
  getProductsByCategory:function(id,callback){
    category.getProductsByCategory(id,(data)=> {
      callback&&callback(data);
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
      title: '零食商贩 Pretty Vendor',
      path: 'pages/category/category'
    }
  }

})