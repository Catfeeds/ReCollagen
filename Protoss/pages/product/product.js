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
        currentTabsIndex:0,
        cartTotalCounts:0,
        currentAttrIndex:-1,
        option_id: -1,
    },
    onLoad: function (option) {
      this.data.id = option.id;
        this._loadData();
    },

    /*加载所有数据*/
    _loadData:function(callback){
        var that = this;
        product.getDetailInfo(this.data.id,(data)=>{
          /*获取数量*/
          var stockArray = [],
            stockArr = data.stock<52?data.stock:52;
            for (let i = 1; i <= stockArr; i++) {
              stockArray.push(i);
            }
            /*重构商品折扣*/
            var discountsArray = [],
              discountsArr = data.discounts;
            for (var key in discountsArr) {
              var discount = (data.price * discountsArr[key].discount / 100).toFixed(2);
                discountsArray.push({
                  discount: discount,
                  quantity: discountsArr[key].quantity
                });
            }

            that.setData({
                cartTotalCounts:cart.getCartTotalCounts().counts1,
                countsArray: stockArray,
                discountsArray: discountsArray,
                price: data.price,
                product:data,
                loadingHidden:true
            });
                        
            /*默认商品选项数量、价格、ID*/
            var optionsArr = data.options;
            if (optionsArr.length > 0){
              var stockArray = [], 
                  options = this._getProductOptions(optionsArr),
                  stockArr = options.stock < 52 ? options.stock : 52;
              for (let i = 1; i <= stockArr; i++) {
                stockArray.push(i);
              }
              that.setData({
                option_id: options.id,
                price: options.price,
                currentAttrIndex: options.index,
                countsArray: stockArray,
              });
            }
            callback&& callback();
        });
    },

    /*规格数量是否大于0来判断默认规格、获取数量和价格*/
    _getProductOptions: function (data) {
      for (let i = 0; i < data.length; i++) {
        if (data[i].stock > 0) {
          return{
            index:i,
            id:data[i].goods_option_id,
            stock: data[i].stock,
            price: data[i].option_price
          };
        }
      }
    },

    //选择购买数目
    bindPickerChange: function(e) {
      var tempPrice,
        float=false,
        counts = this.data.countsArray[e.detail.value],
        discounts = this.data.product.discounts,
        optionsArr = this.data.product.options;

        if (optionsArr.length > 0) {
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
      if (this.data.id) {
        var id = this.data.id,
        haveCollect = this.data.product.haveCollect,
        have = !haveCollect == true ? 1 : 0;
        product.doHaveCollect(id, (data) => {
          this.setData({
            'product.haveCollect': data
          });
        });
      }
    },

    /*添加到购物车*/
    onAddingToCartTap:function(events){
        //防止快速点击
        if(this.data.isFly){
            return;
        }
        this._flyToCartEffect(events);
        this.addToCart();
    },

    /*将商品数据添加到内存中*/
    addToCart:function(){
      var tempObj = {}, keys = ['goods_id', 'name', 'image', 'price', 'isMainGoods', 'haveCollect', 'options','discounts'];
        for(var key in this.data.product){
            if(keys.indexOf(key)>=0){
                tempObj[key]=this.data.product[key];
            }
        }
        cart.add(tempObj, this.data.productCounts, this.data.price, this.data.option_id);
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
            title: '零食商贩 Pretty Vendor',
            path: 'pages/product/product?id=' + this.data.id
        }
    }

})


