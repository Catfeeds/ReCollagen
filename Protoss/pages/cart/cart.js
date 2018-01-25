
import {Cart} from 'cart-model.js';

var cart=new Cart(); //实例化 购物车

Page({
    data: {
        loadingHidden:false,
        selectedCounts:0, //总的商品数
        selectedTypeCounts:0, //总的商品类型数
    },

    onShow:function(){
      cart.getCartDataFromLocal('all',(data) => {
        var newData = cart.getCartTotalCounts(data.goodsList,true); /*重新计算总金额和商品总数*/
        this.setData({
          loadingHidden: true,
          account: newData.account,
          selectedCounts: newData.selectedCounts,
          selectedTypeCounts: newData.selectedTypeCounts,
          cartData: data
        });
      })
    },

    /*更新购物车商品数据*/
    _resetCartData:function(){
      cart.getCartDataFromLocal('all',(data) => {
        var newData = cart.getCartTotalCounts(data.goodsList, true); /*重新计算总金额和商品总数*/
        this.setData({
          account: newData.account,
          selectedCounts: newData.selectedCounts,
          selectedTypeCounts: newData.selectedTypeCounts,
          cartData: data
        });
      })
    },

    /*调整商品数目*/
    changeCounts: function (event) {
      var id = cart.getDataSet(event, 'id'),
        guid = cart.getDataSet(event, 'guid'),
        index = cart.getDataSet(event, 'index'),
        type = cart.getDataSet(event, 'type');
      var counts = this.data.cartData.goodsList[index].count;
      if (type == 'inc') {
        if (counts >= this.data.cartData.goodsList[index].stock) return;
        cart.addCutCounts(id, guid, type, (data) => {
          if (data.errorCode != 0) {
            this.showToast(data.msg);
            return;
          }
          this._resetCartData();
        });
      }
      else 
      {
        if (counts <=1) return;
        cart.addCutCounts(id, guid, type, (data) => {
          if (data.errorCode != 0) {
            this.showToast(data.msg);
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
        index = cart.getDataSet(event, 'index'),
        counts = event.detail.value;

        if (counts <= 1){
          counts = 1;
        }

        if (counts >= this.data.cartData.goodsList[index].stock) {
          counts = this.data.cartData.goodsList[index].stock;
        }

        cart.addInputCounts(id, guid, counts, (data) => {
          if (data.errorCode != 0) {
            this.showToast(data.msg);
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
            this.showToast(data.msg);
            return;
          }
          this._resetCartData();
        });
    },

    /*选择商品*/
    toggleSelect:function(event){
        var id=cart.getDataSet(event,'id'),
          guid = cart.getDataSet(event, 'guid');

        cart.selectStatus(id, guid, (data) => {
          if (data.errorCode != 0) {
            this.showToast(data.msg);
            return;
          }
          this._resetCartData();
        });
    },

    /*全选*/
    toggleSelectAll:function(event){
       var status=cart.getDataSet(event,'status')=='true',
         selectstatus = !status==true?1:-1;
       cart.selectAllStatus(selectstatus, (data) => {
        if (data.errorCode != 0) {
          this.showToast(data.msg);
          return;
        }
        this._resetCartData();
      });
    },

    /*提交订单*/
    submitOrder:function(){
        wx.navigateTo({
            url:'../order/order'
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
  */
  showToast: function (title) {
    wx.showToast({
      title: title,
      icon: 'success',
      duration: 2000
    })
  }
})