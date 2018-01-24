import { Cart } from '../cart/cart-model.js';
import { OrderDetails } from '../order-details/order-details-model.js';
import { UserInfo } from '../userinfo/userinfo-model.js';
import {My} from '../my/my-model.js';

var cart = new Cart();
var order = new OrderDetails();
var userInfo = new UserInfo();
var my=new My();

Page({
    data: {
        pageIndex:1,
        statusId:1,
        currentTabsIndex: 0,
        isLoadedAll:false,
        loadingHidden:false,
        orderArr:[],
    },
    onLoad:function(){
        this._loadData();
    },

    _loadData:function(){
        var that=this;
        my.getUserInfo((data)=>{
            that.setData({
                userInfo:data
            });
        });

        userInfo.getUserAccount((data) => {
          that.setData({
            UserAccount: data
          });
        });

        this._getOrders();
    },

    /*修改或者添加地址信息*/
    editAddress: function () {
      var that = this;
      wx.navigateTo({
        url: '../address/list/list?type=my'
      });
    },

    /*切换订单面板*/
    onTabsItemTap: function (event) {
      var that = this,
        index = order.getDataSet(event, 'index'),
        id = order.getDataSet(event, 'id');
      this.data.orderArr = [];  //订单初始化
      this.setData({
        orderArr: this.data.orderArr,
        statusId:id,
        currentTabsIndex: index,
      });
      this._getOrders(() => {
        that.data.isLoadedAll = false;  //是否加载完全
        that.data.pageIndex = 1;
      });
    },

    /*订单信息*/
    _getOrders:function(callback){
        var that=this;
        order.getOrders(this.data.statusId,this.data.pageIndex,(res)=>{
          var data = res.data;
            that.setData({
                loadingHidden: true
            });
            if(data.length>0) {
                that.data.orderArr.push.apply(that.data.orderArr,data);  //数组合并                
                that.setData({
                    pageIndex: res.current_page,
                    orderArr: that.data.orderArr
                });
            }else{
                that.data.isLoadedAll=true;  //已经全部加载完毕
                that.data.pageIndex=1;
            }
            callback && callback();
        });
    },
    
    /*完善用户资料*/
    userinfo:function(event) {
      wx.navigateTo({
        url: '../userinfo/userinfo?type=my'
      });
    },

    /*显示订单的具体信息*/
    showOrderDetailInfo:function(event){
        var id=order.getDataSet(event,'id');
        wx.navigateTo({
          url:'../order-details/order-details?from=order&id='+id
        });
    },

    /*查看消费记录*/
    gosummary: function (event) {
      wx.navigateTo({
        url: '../summary/summary'
      });
    },

    /*显示收藏商品*/
    showCollectList: function (event) {
      wx.navigateTo({
        url: '../collect/collect'
      });
    },

    /*查看物流*/
    showOrderWul: function (event) {
      var id = order.getDataSet(event, 'id');
      wx.navigateTo({
        url: '../transinfo/transinfo?id=' + id
      });
    },

    /*重新购买订单里的商品*/
    addCart: function (event) {
      var that = this,
        id = order.getDataSet(event, 'id'),
        index = order.getDataSet(event, 'index');
      this.showTipsReturn('提示', '你确定要重新购买吗？', (statusConfirm) => {
        if (statusConfirm) {
          cart.getCartDataFromLocal('all', (data) => {
            if (data.goodsList == "") {
              that.addToCart(id, index);
            }
            else 
            {
              that.showTipsReturn('提示', '购物车里已有商品，需清空之后才能再次购买？', (statusConfirm) => {
                if (statusConfirm) {
                  
                  if (cartData.length < 1) {
                    that.addToCart(id, index);
                  }
                  else
                  {
                    that.showTips('提示', '清空购物车失败');
                  }
                }
              })
            }
          })
        }
      })
    },

    /*将商品添加到购物车*/
    addToCart: function (id,index) {
      var that = this;
      order.getOrderInfo(id, (data) => {
        var item,
          arr = data.products;
        for (let i = 0; i < arr.length; i++) {
          item = arr[i];
          cart.add(item.goods_id, item.option_id, item.counts, (data) => {
            if ((i + 1) == arr.length) {
              // order.cancel(id, (statusCode) => {
              //   if (statusCode.errorCode != 0) {
              //     that.showTips('订单提示', statusCode.msg);
              //     return;
              //   }
                that.data.orderArr[index].order_status = 5;
                that.data.orderArr.splice(index, 1);
                that.setData({
                  orderArr: that.data.orderArr
                });
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
              // });
            }
          });
        }
      });
    },

    

    /*取消订单*/
    cancel: function (event) {
      var that = this, 
        id = order.getDataSet(event, 'id'),
        index = order.getDataSet(event, 'index');
      this.showTipsReturn('提示', '你确定要取消订单吗？', (statusConfirm) => {
        if (statusConfirm){
          order.cancel(id, (statusCode) => {
            if (statusCode.errorCode != 0) {
              that.showTips('订单提示', statusCode.msg);
              return;
            }
            that.data.orderArr[index].order_status = 5;
            that.setData({
              orderArr: that.data.orderArr
            });
          });
        }
      })
    },

    /*确认收货*/
    receive: function (event) {
      var that = this,
        id = order.getDataSet(event, 'id'),
        index = order.getDataSet(event, 'index');
      this.showTipsReturn('提示', '你确认要收货吗？', (statusConfirm) => {
        if (statusConfirm) {
          order.receive(id, (statusCode) => {
            if (statusCode.errorCode != 0) {
              that.showTips('订单提示', statusCode.msg);
              return;
            }
            that.data.orderArr[index].order_status = 4;
            that.setData({
              orderArr: that.data.orderArr
            });
          });
        }
      });
    },

    /*未支付订单再次支付*/
    rePay:function(event){
        var id=order.getDataSet(event,'id'),
            index=order.getDataSet(event,'index');
        this._execPay(id,index);
    },

    /*支付*/
    _execPay:function(id,index){
        var that=this;
        order.execPay(id,(statusCode)=>{
          if (statusCode.errorCode != 0) {
            that.showTips('支付提示', statusCode.msg);
            return;
          }
          that.data.orderArr[index].order_status = 2;
          that.setData({
            orderArr: that.data.orderArr
          });
          wx.navigateTo({
            url: '../pay-result/pay-result?id=' + id + '&from=my'
          });
        });
    },

    onReachBottom:function(){
        if(!this.data.isLoadedAll) {
            this.data.pageIndex++;
            this._getOrders();
        }
    },

    /*
     * 提示窗口
     * params:
     * title - {string}标题
     * content - {string}内容
     */
    showTips:function(title,content){
        wx.showModal({
            title: title,
            content: content,
            showCancel:false,
            success: function(res) {

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
})