import { Legalrules } from '../legalrules/legalrules-model.js';
var legalrules = new Legalrules();
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
        legalrules.getLegalrulesTxt((data) => {
          if (data && data != "") {
            WxParse.wxParse('article', 'html', data, that, 5);
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
        path: 'pages/legalrules/legalrules'
      }
    }
})