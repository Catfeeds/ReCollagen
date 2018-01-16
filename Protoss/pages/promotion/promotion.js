import { Promotion } from '../promotion/promotion-model.js';
var promotion = new Promotion();
var WxParse = require('../../wxParse/wxParse.js');
Page({
    data: {
        loadingHidden:false,
    },
    onLoad:function(){
        this._loadData();
    },

    _loadData:function(){
        var that=this;
        promotion.getPromotionTxt((data) => {
          console.log(data.description)
          that.setData({
            detiel: WxParse.wxParse('article', 'html', data.description, that, 5),
            loadingHidden: true
          });
        });
    },

    /*下拉刷新页面*/
    onPullDownRefresh: function () {
      this._loadData(() => {
        wx.stopPullDownRefresh()
      });
    },

    //分享效果
    onShareAppMessage: function () {
      return {
        title: '悦寇霖智',
        path: 'pages/promotion/promotion'
      }
    }
})