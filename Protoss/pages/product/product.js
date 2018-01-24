// var productObj = require('product-model.js');

import {Product} from 'product-model.js';
import {Cart} from '../cart/cart-model.js';

var product=new Product();  //实例化 商品详情 对象
var cart=new Cart();
Page({
    data: {
        loadingHidden:false,
        hiddenSmallImg:true,
        countsArray: [],
        productCounts:1,
        cartTotalCounts:0,
        stockCount: 0,
        option_id: 0,
        currentTabsIndex: 0,
        currentAttrIndex:-1,
    },
    onLoad: function (option) {
      this.data.id = option.id;
      this._loadData();
    },

    /*加载所有数据*/
    _loadData:function(callback){
        var that = this;
        product.getDetailInfo(this.data.id,(data)=>{

            /*判断是否是单商品*/
          var optionsCount = data.options;
          if (optionsCount.length < 1) {

              /*获取数量*/
              var stockArray = [],
                stockCount = data.stock<52?data.stock:52;
              for (let i = 1; i <= stockCount; i++) {
                stockArray.push(i);
              }

              /*单商品优惠信息*/
              var discountsArray = [],
                discountsCount = data.discounts;
              for (var key in discountsCount) {
                var discount = (data.price * discountsCount[key].discount / 100).toFixed(2);
                  discountsArray.push({
                    discount: discount,
                    quantity: discountsCount[key].quantity
                  });
              }

              that.setData({
                  countsArray: stockArray,
                  discountsArray: discountsArray,
                  price: data.price,
                  stockCount: stockCount,
              });

            }
            else 
            {
              /*默认商品选项数量、价格、ID*/
              var stockArray = [], 
              options = this._getProductOptions(optionsCount),
                stockCount = options.stock < 52 ? options.stock : 52;
              for (let i = 1; i <= stockCount; i++) {
                stockArray.push(i);
              }
              that.setData({
                option_id: options.id,
                price: options.price,
                currentAttrIndex: options.index,
                countsArray: stockArray,
                stockCount: stockCount,
              });
            }
            that.setData({
              cartTotalCounts: 10,
              product: data,
              loadingHidden: true
            });

            callback&& callback();
        });
    },

    /*规格数量是否大于0来判断默认规格、获取数量和价格*/
    _getProductOptions: function (data) {
      var item,
      result = {
        index: -1,
        id: data[0].goods_option_id,
        stock: data[0].stock,
        price: data[0].option_price
      };
      for (let i = 0; i < data.length; i++) {
        item = data[i];
        if (item.stock > 0) {
          result = {
            index: i,
            id: item.goods_option_id,
            stock: item.stock,
            price: item.option_price
          };
          break;
        }
      }
      return result;
    },

    //选择购买数目
    bindPickerChange: function(e) {
      var tempPrice,
        float=false,
        counts = this.data.countsArray[e.detail.value],
        discounts = this.data.product.discounts,
        optionsCount = this.data.product.options;

      if (optionsCount.length > 0) {
          tempPrice = this.data.price;
        }
        else
        {
          discounts.sort(function (a, b) {
            return a.quantity - b.quantity;
          });
          for (var key in discounts) {
            if (counts >= discounts[key].quantity) {
              tempPrice = (this.data.product.price * discounts[key].discount / 100).toFixed(2);
              float = true;
            }
          }
          tempPrice = float == true ? tempPrice : this.data.product.price;
        }
        this.setData({
          price: tempPrice,
          productCounts: counts,
        })
    },

    //切换规格
    onClickAttr: function (event) {
      var id = product.getDataSet(event, 'id');
      var index = product.getDataSet(event, 'index');
      var price = product.getDataSet(event, 'price');
      var stock = product.getDataSet(event, 'stock');
      var stockArray = [],
          stockArr = stock < 52 ? stock : 52;
      for (let i = 1; i <= stockArr; i++) {
        stockArray.push(i);
      }
      this.setData({
        countsArray: stockArray,
        option_id:id,
        price: price,
        currentAttrIndex: index
      });
    },

    /*根据商品id得到 商品所在下标*/
    _getProductIndexById: function (data,id) {
      var len = data.length;
      for (let i = 0; i < len; i++) {
        if (data[i].goods_id == id) {
          return i;
        }
      }
    },

    //切换详情面板
    onTabsItemTap:function(event){
        var index=product.getDataSet(event,'index');
        this.setData({
            currentTabsIndex:index
        });
    },

    /*添加到收藏*/
    onAddingToHaveTap: function (events) {
      var that = this;
      if (this.data.id) {
        var id = this.data.id;
        product.doHaveCollect(id, (data) => {
          if (data.errorCode != 0) {
            that.showTips('收藏提示', data.msg);
            return;
          }
          var haveCollect = data.code == 201 ? 1 : 0;
          that.setData({
            'product.haveCollect': haveCollect
          });
        });
      }
    },

    /*添加到购物车*/
    onAddingToCartTap:function(events){
        if (this.data.product.status==0){
          this.showTips('加入购物车提示', '此商品已下架，不能购买');
          return;
        }
        //防止快速点击
        if(this.data.isFly){
            return;
        }
        this._flyToCartEffect(events);
        this.addToCart();
    },

    /*将商品添加到购物车*/
    addToCart:function(){
      cart.add(this.data.id, this.data.option_id, this.data.productCounts, (data) => {
        if (data.errorCode != 0) {
          that.showTips('加入购物车', data.msg);
          return;
        }
      });
    },

    /*加入购物车动效*/
    _flyToCartEffect:function(events){
        //获得当前点击的位置，距离可视区域左上角
        var touches=events.touches[0];
        var diff={
                x:'25px',
                y:25-touches.clientY+'px'
            },
            style='display: block;-webkit-transform:translate('+diff.x+','+diff.y+') rotate(350deg) scale(0)';  //移动距离
        this.setData({
            isFly:true,
            translateStyle:style
        });
        var that=this;
        setTimeout(()=>{
            that.setData({
                isFly:false,
                translateStyle:'-webkit-transform: none;',  //恢复到最初状态
                isShake:true,
            });
            setTimeout(()=>{
                var counts=that.data.cartTotalCounts+that.data.productCounts;
                that.setData({
                    isShake:false,
                    cartTotalCounts:counts
                });
            },200);
        },1000);
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
    },

    /*跳转到购物车*/
    onCartTap:function(){
        wx.switchTab({
            url: '/pages/cart/cart'
        });
    },

    /*下拉刷新页面*/
    onPullDownRefresh: function(){
        this._loadData(()=>{
            wx.stopPullDownRefresh()
        });
    },

    //分享效果
    onShareAppMessage: function () {
        return {
          title: '悦寇霖智',
            path: 'pages/product/product?id=' + this.data.id
        }
    }

})


