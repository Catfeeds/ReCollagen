import {Address} from '../../utils/address.js';
import {Order} from '../order/order-model.js';
import {My} from '../my/my-model.js';

var address=new Address();
var order=new Order();
var my=new My();

Page({
    data: {
        pageIndex:1,
        isLoadedAll:false,
        loadingHidden:false,
        orderArr:[],
        addressInfo:null
    },
    onLoad:function(){
        this._loadData();

        /*显示收获地址*/
        this._addressInfo();
    },

    onShow:function(){
        if(this.data.loadingHidden){
            this.onPullDownRefresh();
        }

        /*显示收获地址*/
        this._addressInfo();
    },

    _loadData:function(){
        var that=this;
        my.getUserInfo((data)=>{
            that.setData({
                userInfo:data
            });
        });

        my.getUserInfo((data) => {
          that.setData({
            userInfo: data
          });
        });

        my.getUserAccount((data) => {
          that.setData({
            UserAccount: data
          });
        });

        this._getOrders();
    },
    
    /*显示收获地址*/
    _addressInfo: function () {
      address.getAddress((res) => {
        this.setData({
          addressInfo: res,
          'addressInfo.address': res.province + res.city + res.country + res.address
        })
      });
    },

    /*修改或者添加地址信息*/
    editAddress: function () {
      var that = this;
      wx.navigateTo({
        url: '../address/address'
      });
    },

    /*绑定地址信息*/
    _bindAddressInfo:function(addressInfo){
        this.setData({
            addressInfo: addressInfo
        });
    },

    /*订单信息*/
    _getOrders:function(callback){
        var that=this;
        order.getOrders(this.data.pageIndex,(data)=>{
            that.setData({
                loadingHidden: true
            });
            if(data.length>0) {
                that.data.orderArr.push.apply(that.data.orderArr,data);  //数组合并                
                that.setData({
                    orderArr: that.data.orderArr
                });
            }else{
                that.data.isLoadedAll=true;  //已经全部加载完毕
                that.data.pageIndex=1;
            }
            callback && callback();
        });
    },

    /*显示订单的具体信息*/
    showOrderDetailInfo:function(event){
        var id=order.getDataSet(event,'id');
        wx.navigateTo({
            url:'../order/order?from=order&id='+id
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

    /*取消订单*/
    cancel: function (event) {
      var that = this, 
        id = order.getDataSet(event, 'id'),
        index = order.getDataSet(event, 'index');
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
    },

    /*确认收货*/
    receive: function (event) {
      var that = this,
        id = order.getDataSet(event, 'id'),
        index = order.getDataSet(event, 'index');
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
    },

    /*未支付订单再次支付*/
    rePay:function(event){
        var id=order.getDataSet(event,'id'),
            index=order.getDataSet(event,'index');

        //online 上线实例，屏蔽支付功能
        if(order.onPay) {
            this._execPay(id,index);
        }else {
            this.showTips('支付提示','用户已被禁用');
        }
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

    /*下拉刷新页面*/
    onPullDownRefresh: function(){
        var that=this;
        this.data.orderArr=[];  //订单初始化
        this._getOrders(()=>{
            that.data.isLoadedAll=false;  //是否加载完全
            that.data.pageIndex=1;
            wx.stopPullDownRefresh();
        });
    },


    // onReachBottom:function(){
    //     if(!this.data.isLoadedAll) {
    //         this.data.pageIndex++;
    //         this._getOrders();
    //     }
    // },

    /*
     * 提示窗口
     * params:
     * title - {string}标题
     * content - {string}内容
     * flag - {bool}是否跳转到 "我的页面"
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

})