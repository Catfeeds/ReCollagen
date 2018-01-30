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
          if (data.description && data.description!=""){
            WxParse.wxParse('article', 'html', data.description, that, 5);
          }
          that.setData({
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
        title: '悦蔻霖智',
        path: 'pages/promotion/promotion'
      }
    }
})