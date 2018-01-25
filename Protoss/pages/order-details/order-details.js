import { OrderDetails } from 'order-details-model.js';
import {Cart} from '../cart/cart-model.js';
import { AdrList } from '../address/list/list-model.js';

var order = new OrderDetails();
var cart=new Cart();
var adrList = new AdrList();

Page({
        data: {
            addressInfo:null
        },

        onLoad: function (options) {
            this.data.id=options.id;
        },

        onShow:function(){
          var that = this;
            if(this.data.id) {
                var id = this.data.id;
                order.getOrderInfo(id, (data)=> {
                  console.log(data)
                    that.setData({
                        orderStatus: data.order_status,
                        productsArr: data.products,
                        basicInfo: {
                            orderTime: data.create_time,
                            orderNo: data.order_num_alias
                        },
                        wuliuInfo: {
                          orderMethod: data.shipping_method,
                          orderNum: data.shipping_num
                        },
                        addressInfo: {
                          name: data.shipping_name,
                          telephone: data.shipping_tel,
                          address: data.shipping_addr,
                        },
                        promotion: data.promotion,
                        mainGoodsPrice: data.mainGoodsPrice,
                        otherGoodsPrice: data.otherGoodsPrice,
                        shippingPrice: data.shippingPrice,
                        total: data.total,
                    });
                });
            }
        },
        
        /*查看物流*/
        showOrderWul: function (event) {
          var id = this.data.id;
          wx.navigateTo({
            url: '../transinfo/transinfo?id=' + id
          });
        },

        /*取消订单*/
        cancel: function (event) {
          var that = this,
            id = this.data.id;
          this.showTipsReturn('提示', '你确定要取消订单吗？', (statusConfirm) => {
            if (statusConfirm) {
              order.cancel(id, (statusCode) => {
                if (statusCode.errorCode != 0) {
                  that.showTips('订单提示', statusCode.msg);
                  return;
                }
                that.setData({
                  orderStatus: 5
                });
              });
            }
          })
        },

        /*确认收货*/
        receive: function (event) {
          var that = this,
            id = this.data.id;
          this.showTipsReturn('提示', '你确认要收货吗？', (statusConfirm) => {
            if (statusConfirm) {
              order.receive(id, (statusCode) => {
                if (statusCode.errorCode != 0) {
                  that.showTips('订单提示', statusCode.msg);
                  return;
                }
                that.setData({
                  orderStatus: 4
                });
              });
            }
          });
        },

        /*付款*/
        pay:function(){
            this._execPay(this.data.id);
        },

        /*
        *开始支付
        * params:
        * id - {int}订单id
        */
        _execPay:function(id){
            var that=this;
            order.execPay(id,(statusCode)=>{
              if (statusCode.errorCode != 0) {
                that.showTips('支付提示', statusCode.msg);
                return;
              }
              wx.navigateTo({
                  url: '../pay-result/pay-result?id=' + id + '&from=order'
              });
            });
        },

        /*
        * 提示窗口
        * params:
        * title - {string}标题
        * content - {string}内容
        * flag - {bool}是否跳转到 "我的页面"
        */
        showTips: function (title, content, flag) {
          wx.showModal({
            title: title,
            content: content,
            showCancel: false,
            success: function (res) {
              if (flag) {
                wx.switchTab({
                  url: '/pages/my/my'
                });
              }
            }
          });
        },

        /*
        * 提示窗口 - 返回值
        * params:
        * title - {string}标题
        * content - {string}内容
        * callback - {bool}返回值
        */
        showTipsReturn: function (title, content, callback) {
          wx.showModal({
            title: title,
            content: content,
            showCancel: true,
            success: function (res) {
              callback && callback(res.confirm);
            }
          });
        },
    }
)