import { OrderDetails } from '../order-details/order-details-model.js';
var order = new OrderDetails();

Page({
        data: {

        },
        onReady: function () {
          var titleName = this.data.from == 'order' ? '待付款' : ' 支付结果';
          wx.setNavigationBarTitle({
            title: titleName
          });
        },
        onLoad: function (options){
          var titleName = options.from == 'order' ? '待付款' : ' 支付结果';
          wx.setNavigationBarTitle({
            title: titleName
          });
          this.setData({
            id: options.id,
            title: options.from == 'order' ? '待付款' : ' 支付结果',
            from: options.from
          });
        },

        viewOrder:function(){
          if (this.data.from == 'my') {
            //返回上一级
            wx.navigateBack({
              delta: 1
            })

          } else {
            // wx.switchTab({
            //   url: '../my/my',
            //   success: function (e) {
            //     var page = getCurrentPages().pop();
            //     if (page == undefined || page == null) return;
            //     page.onLoad();
            //   } 
            // })
            wx.redirectTo({
              url: '../order-details/order-details?id=' + this.data.id
            });
          }
        },

        /*付款*/
        pay: function () {
          this._execPay(this.data.id);
        },

        /*
        *开始支付
        * params:
        * id - {int}订单id
        */
        _execPay: function (id) {
          var that = this;
          order.execPay(id, (statusCode) => {
            if (statusCode.errorCode != 0) {
              that.showTips('支付提示', statusCode.msg);
              return;
            }
            wx.redirectTo({
              url: '../pay-result/pay-result?id=' + id + '&from=pay'
            });
          });
        },

    }
)