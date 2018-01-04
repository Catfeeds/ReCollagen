import { Home } from 'home-model.js';
var home = new Home(); //实例化 首页 对象
Page({
    data: {
        loadingHidden: false
    },
    onLoad: function () {
        this._loadData();
    },

    /*加载所有数据*/
    _loadData:function(callback){
        var that = this;

        // 获得bannar信息
        home.getBannerData((data) => {
            that.setData({
                bannerArr: data
            });
        });

        /*获取最新促销*/
        home.getPromotionData((data) => {
            that.setData({
                promotionArr: data,
                loadingHidden: true
            });
        });

        /*获取单品信息*/
        home.getProductorData((data) => {
            that.setData({
                productsArr: data,
                loadingHidden: true
            });
            callback&&callback();
        });
    },

    /*跳转到商品详情*/
    onProductsItemTap: function (event) {
        var id = home.getDataSet(event, 'id');
        wx.navigateTo({
            url: '../product/product?id=' + id
        })
    },

    /*跳转到促销文案*/
    onPromotionTap: function (event) {
        wx.navigateTo({
          url: '../promotion/promotion'
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
            path: 'pages/home/home'
        }
    }

})


