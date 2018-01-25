
Page({
        data: {

        },
        onReady: function () {
          var titleName = this.data.from == 'order' ? '已下订单' : ' 支付结果';
          wx.setNavigationBarTitle({
            title: titleName
          });
        },
        onLoad: function (options){
          var titleName = options.from == 'order' ? '已下订单' : ' 支付结果';
          wx.setNavigationBarTitle({
            title: titleName
          });
          this.setData({
            id: options.id,
            title: options.from == 'order' ? '已下订单' : ' 支付结果',
            from: options.from
          });
        },

        viewOrder:function(){
          if (this.data.from == 'order') {
            wx.switchTab({
              url: '../my/my',
              success: function (e) {
                var page = getCurrentPages().pop();
                if (page == undefined || page == null) return;
                page.onLoad();
              } 
            })
          } else {
            //返回上一级
            wx.navigateBack({
              delta: 1
            })
          }
        }
    }
)