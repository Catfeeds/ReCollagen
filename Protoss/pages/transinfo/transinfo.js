import { Transinfo } from 'transinfo-model.js';

var transinfo = new Transinfo();

Page({
  data: {
    loadingHidden: false
  },
  onLoad: function (option) {
    this.data.id = option.id;
    this._loadData();
  },

  /*加载所有数据*/
  _loadData: function (callback) {
    var that = this;
    
    /*获取物流信息*/
    transinfo.getTransinfo(this.data.id,(data) => {
      that.setData({
        transinfoData: data,
        loadingHidden: true
      });
      callback && callback();
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
      path: 'pages/transinfo/transinfo'
    }
  }

})