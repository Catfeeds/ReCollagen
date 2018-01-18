// var CartObj = require('cart-model.js');

import {Cart} from 'cart-model.js';

var cart=new Cart(); //实例化 购物车

Page({
    data: {
        loadingHidden:false,
        selectedCounts:0, //总的商品数
        selectedTypeCounts:0, //总的商品类型数
    },


    onShow:function(){
      this._loadData();
    },

    _loadData: function () {
      cart.getCartDataFromLocal((data) => {
        this.setData({
          loadingHidden: true,
          cartData: data
        });
      })
    },

    /*更新购物车商品数据*/
    _resetCartData:function(){
        this._loadData();
        var newData = this._calcTotalAccountAndCounts(this.data.cartData.goodsList); /*重新计算总金额和商品总数*/
        this.setData({
            account: newData.account,
            selectedCounts:newData.selectedCounts,
            selectedTypeCounts:newData.selectedTypeCounts,
        });
    },

    /*
    * 计算总金额和选择的商品总数
    * */
    _calcTotalAccountAndCounts:function(data){
        var len=data.length,
            account=0,
            selectedCounts=0,
            selectedTypeCounts=0;
        let multiple=100;
        for(let i=0;i<len;i++){
            //避免 0.05 + 0.01 = 0.060 000 000 000 000 005 的问题，乘以 100 *100
          if (data[i].isChecked==1) {
                account += data[i].count * multiple * Number(data[i].totalPrice) * multiple;
                selectedCounts += data[i].count;
                selectedTypeCounts++;
          }
        }
        return{
            selectedCounts:selectedCounts,
            selectedTypeCounts:selectedTypeCounts,
            account: (account/(multiple*multiple)).toFixed(2)
        }
    },


    /*调整商品数目*/
    changeCounts: function (event) {
      var id = cart.getDataSet(event, 'id'),
        guid = cart.getDataSet(event, 'guid'),
        type = cart.getDataSet(event, 'type');
      if (type == 'inc') {
        cart.addCutCounts(id, guid, type, (data) => {
          if (data.errorCode != 0) {
            this.showTips('商品数量', data.msg);
            return;
          }
          this._resetCartData();
        });
      } 
      else 
      {
        cart.addCutCounts(id, guid, type, (data) => {
          if (data.errorCode != 0) {
            this.showTips('商品数量', data.msg);
            return;
          }
          this._resetCartData();
        }); 
      }
    },

    //输入商品数量
    changeInput: function (event) {
      var id = cart.getDataSet(event, 'id'),
        guid = cart.getDataSet(event, 'guid'),
        counts = event.detail.value;

        cart.addInputCounts(id, guid, counts, (data) => {
          if (data.errorCode != 0) {
            this.showTips('商品数量', data.msg);
            return;
          }
          this._resetCartData();
        });
    },

    /*删除商品*/
    delete:function(event){
      var id=cart.getDataSet(event,'id'),
        guid = cart.getDataSet(event, 'guid');

        cart.delete(id, guid, (data) => {
          if (data.errorCode != 0) {
            this.showTips('删除商品', data.msg);
            return;
          }
          this._resetCartData();
        });
    },

    /*选择商品*/
    toggleSelect:function(event){
        var id=cart.getDataSet(event,'id'),
            guid = cart.getDataSet(event, 'guid'),
            status=cart.getDataSet(event,'status');
        

    },

    /*全选*/
    toggleSelectAll:function(event){
        var status=cart.getDataSet(event,'status')=='true';
        var data=this.data.cartData,
            len=data.length;
        for(let i=0;i<len;i++) {
            data[i].selectStatus=!status;
        }
        this._resetCartData();
    },

    /*提交订单*/
    submitOrder:function(){
        wx.navigateTo({
            url:'../order/order?account='+this.data.account+'&from=cart'
        });
    },

    /*查看商品详情*/
    onProductsItemTap:function(event){
        var id = cart.getDataSet(event, 'id');
        wx.navigateTo({
            url: '../product/product?id=' + id
        })
    },

    /*
    * 提示窗口
    * params:
    * title - {string}标题
    * content - {string}内容
    * flag - {bool}是否跳转到 "我的页面"
    */
    showTips: function (title, content) {
      wx.showModal({
        title: title,
        content: content,
        showCancel: false,
        success: function (res) {
        }
      });
    }
})