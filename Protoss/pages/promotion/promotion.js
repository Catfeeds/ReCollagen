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
})