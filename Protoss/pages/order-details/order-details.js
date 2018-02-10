import { OrderDetails } from 'order-details-model.js';
import {Cart} from '../cart/cart-model.js';
import { AdrList } from '../address/list/list-model.js';

var order = new OrderDetails();
var cart=new Cart();
var adrList = new AdrList();

Page({
        data: {
            loadingHidden: false,
            orderHidden: true,
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
                    that.data.productsArr = [];
                    that.setData({
                        loadingHidden: true,
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
                        userRemarks: data.userRemarks,
                        shopRemarks: data.shopRemarks,
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

        /*修改订单*/
        addCart: function (event) {
          var that = this,
            id = this.data.id;
          this.showTipsReturn('提示', '你确定要修改订单吗？', (statusConfirm) => {
            if (statusConfirm) {

              cart.getCartDataFromLocal('all', (data) => {
                if (data.goodsList == "") {
                  that.addToCart(id);
                }
                else {
                  that.showTipsReturn('提示', '购物车里已有商品，需清空之后才能再次购买？', (statusConfirm) => {
                    if (statusConfirm) {
                      cart.delAll((statusCode) => {
                        if (statusCode.errorCode != 0) {
                          that.showTips('提示', statusCode.msg);
                          return;
                        }
                        that.addToCart(id);
                      })
                    }
                  })
                }
              })
            }
          })
        },

        /*将商品添加到购物车*/
        addToCart: function (id) {
          var that = this;
            var item,
              arr = that.data.productsArr;
            for (let i = 0; i < arr.length; i++) {
              item = arr[i];
              cart.add(item.goods_id, item.option_id, item.counts, (data) => {
                if ((i + 1) == arr.length) {
                  order.orderDel(id, (statusCode) => {
                    if (statusCode.errorCode != 0) {
                      that.showTips('订单提示', statusCode.msg);
                      return;
                    }
                    wx.showModal({
                      title: '',
                      content: '已加入到购物车',
                      showCancel: false,
                      success: function (res) {
                        wx.switchTab({
                          url: '/pages/cart/cart'
                        });
                      }
                    });
                  });
                }
              });
            }
        },

        /**/

        /*付款*/
        pay:function(){
            this.setData({
              orderHidden: false,
            });
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
              that.setData({
                orderHidden: true,
              });
              if (statusCode.errorCode != 0) {
                that.showTips('支付提示', statusCode.msg);
                return;
              }
              wx.navigateTo({
                  url: '../pay-result/pay-result?id=' + id + '&from=my'
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