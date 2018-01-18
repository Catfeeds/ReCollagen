import { Summary } from 'summary-model.js';

var summary = new Summary();

Page({
  data: {
    pageIndex: 1,
    isLoadedAll: false,
    loadingHidden: false
  },
  onLoad: function () {
    this._loadData();
  },

  /*加载所有数据*/
  _loadData: function (callback) {
    var that = this;

    /*获金额消费记录*/
    var that = this;
    summary.getSummary(10, this.data.pageIndex, (res) => {
      var data = res.data;
      that.setData({
        loadingHidden: true
      });
      if (data.length > 0) {
        that.data.push.apply(that.data, data);  //数组合并                
        that.setData({
          pageIndex: res.current_page,
          summaryData: res.data
        });
      } else {
        that.data.isLoadedAll = true;  //已经全部加载完毕
        that.data.pageIndex = 1;
      }
      callback && callback();
    });
  },

   


  /*下拉刷新页面*/
  onReachBottom: function () {
    if (!this.data.isLoadedAll) {
      this.data.pageIndex++;
      this._loadData();
    }
  },

  //分享效果
  onShareAppMessage: function () {
    return {
      title: '悦蔻霖智',
      path: 'pages/summary/summary'
    }
  }

})