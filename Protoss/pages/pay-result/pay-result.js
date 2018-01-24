
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
          wx.redirectTo({
              url: '../order/order?from=order&id=' + this.data.id
          });
        }
    }
)