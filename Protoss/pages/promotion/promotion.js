import { Promotion } from '../promotion/promotion-model.js';

var promotion = new Promotion();

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
          that.setData({
            PromotionTxt: data,
            loadingHidden: true
          });
          callback && callback();
        });
    },
})