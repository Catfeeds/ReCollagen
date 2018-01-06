import { Collect } from 'collect-model.js';
import { Product } from '../product/product-model.js';

var collect = new Collect(); //实例化  收藏列表 对象
var product = new Product();  //实例化 商品详情 对象

Page({
  data: {
    loadingHidden: false
  },
  onLoad: function (option) {
    this._loadData();
  },

  /*加载所有数据*/
  _loadData: function (callback) {
    var that = this;
    
    /*获取收藏列表信息*/
    collect.getCollectList((data) => {
      that.setData({
        collectData: data,
        loadingHidden: true
      });
      callback && callback();
    });
  },

  /*删除到收藏*/
  onDeleteToHaveTap: function (event) {
    var that = this;
    var id = collect.getDataSet(event, 'id'),
      Index = collect.getDataSet(event, 'index');
    if (id) {
      product.doHaveCollect(id, (data) => {
        if (data.code!=202){
          that.showToast('删除失败');
          return;
        }
        that.data.collectData.splice(Index, 1);//删除某一项商品
        that.setData({
          collectData: that.data.collectData,
          loadingHidden: true
        });
      });
    }
  },

  /*跳转到商品详情*/
  onProductsItemTap: function (event) {
    var id = collect.getDataSet(event, 'id');
    wx.navigateTo({
      url: '../product/product?id=' + id
    })
  },

  /*下拉刷新页面*/
  onPullDownRefresh: function () {
    this._loadData(() => {
      wx.stopPullDownRefresh()
    });
  },

  /*
* 提示窗口
* params:
* title - {string}标题
*/
  showToast: function (title) {
    wx.showToast({
      title: title,
      icon: 'success',
      duration: 2000
    })
  },

  //分享效果
  onShareAppMessage: function () {
    return {
      title: '悦蔻霖智',
      path: 'pages/collect/collect'
    }
  }

})