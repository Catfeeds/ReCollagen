import { Summary } from 'summary-model.js';

var summary = new Summary();

Page({
  data: {
    loadingHidden: false
  },
  onLoad: function () {
    this._loadData();
  },

  /*加载所有数据*/
  _loadData: function (callback) {
    var that = this;

    /*获取物流信息*/
    summary.getSummary(10,1, (res) => {
      that.setData({
        summaryData: res.data,
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
      path: 'pages/summary/summary'
    }
  }

})