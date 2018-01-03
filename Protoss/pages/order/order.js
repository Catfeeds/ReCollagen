import {Order} from '../order/order-model.js';
import {Cart} from '../cart/cart-model.js';
import { AdrList } from '../address/list/list-model.js';

var order=new Order();
var cart=new Cart();
var adrList = new AdrList();

Page({
        data: {
            fromCartFlag:true,
            addressInfo:null,
            shippingPrice:0
        },

        /*
        * 订单数据来源包括两个：
        * 1.购物车下单
        * 2.旧的订单
        * */
        onLoad: function (options) {
            var flag=options.from=='cart',
                that=this;
            this.data.fromCartFlag=flag;
            this.data.account=options.account;

            //来自于购物车
            if(flag) {
                this.setData({
                    productsArr: cart.getCartDataFromLocal(true),
                    account:options.account,
                    orderStatus:0
                });

                /*显示收获地址*/
                this._addressInfo(0);     

                /*主商品价格和辅销品价格*/
                var accountMain = this._calcTotalMainAndCounts(this.data.productsArr,1).account;
                var accountFain = this._calcTotalMainAndCounts(this.data.productsArr,0).account;
                

                /*获取促销套餐*/
                order.getMainPromotion((data) => {
                  var Promotion = [];
                  for (let i = 1; i < 4; i++) {
                    var dataArr = that.getMainPromotionTypeInfo(data, i, accountMain);
                    if (dataArr) {
                      Promotion.push({
                        id: dataArr.id,
                        name: dataArr.name,
                        type: dataArr.type,
                        expression: dataArr.expression,
                      });
                    }
                  }
                  /*获取满额打折价格*/
                  var PromotionPrice = that.getMainPromotionTypePrice(Promotion);

                  /*获取商品重量*/
                  var weight = this.getResultweight(this.data.productsArr);

                  /*获取运费信息*/                    
                  order.getTransFee(weight, this.data.addressInfo.address_id, (res) => {
                    
                    /*设置总参数*/
                    that.setData({
                      mainGoodsPrice: (accountMain * PromotionPrice).toFixed(2),
                      otherGoodsPrice: accountFain,
                      shippingPrice: res.fee,
                      shippingtransId: res.transId,
                      PromotionInfo: Promotion,
                    });

                    /*获取商品总价格*/
                    var ResultTotal = that.getResultTotal(that.data.mainGoodsPrice, that.data.otherGoodsPrice, that.data.shippingPrice);
                    that.setData({
                      total: ResultTotal
                    });

                  });
                })
            }
            //旧订单
            else{
                this.data.id=options.id;
            }

        },

        onShow:function(){
          var that = this;
            if(this.data.id) {
                //下单后，支付成功或者失败后，点左上角返回时能够更新订单状态 所以放在onshow中
                var id = this.data.id;
                order.getOrderInfoById(id, (data)=> {
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
                        PromotionInfo: data.promotionName,
                        mainGoodsPrice: data.mainGoodsPrice,
                        otherGoodsPrice: data.otherGoodsPrice,
                        shippingPrice: data.shippingPrice,
                        total: data.total,
                    });
                });
            }
            else
            {
              /*显示收获地址*/
              adrList.getCartDataFromLocal((id) => {
                if (id){
                  that._addressInfo(id);
                  /*获取商品重量*/
                  var weight = that.getResultweight(that.data.productsArr);
                  /*获取运费信息*/
                  order.getTransFee(weight, id, (res) => {
                    /*设置总参数*/
                    that.setData({
                      shippingPrice: res.fee,
                      shippingtransId: res.transId,
                    });
                    /*获取商品总价格*/
                    var ResultTotal = that.getResultTotal(that.data.mainGoodsPrice, that.data.otherGoodsPrice, that.data.shippingPrice);
                    that.setData({
                      total: ResultTotal
                    });
                  });
                }
              })
            }
        },

        /*显示收获地址*/
        _addressInfo: function (id) {
          var that = this;
          adrList.getAddress((res) => {
            var hasInfo = adrList._isHasThatOne(id, res);
            if (hasInfo.index != -1) {
              hasInfo.data.address = hasInfo.data.province + ',' + hasInfo.data.city + ',' + hasInfo.data.country + ',' + hasInfo.data.address;
              that.setData({
                addressInfo: hasInfo.data
              })
            }
          });
        },
        
        /*选择地址*/
        changAddress: function () {
          var that = this;
          wx.navigateTo({
            url: '../address/list/list?type=order'
          });
        },

        /*查看物流*/
        showOrderWul: function (event) {
          var id = this.data.id;
          wx.navigateTo({
            url: '../transinfo/transinfo?id=' + id
          });
        },

        /*
        * 计算商品总金额
        * */
        getResultTotal: function (num1, num2, num3){
          return (parseInt(num1) + parseInt(num2) + parseInt(num3)).toFixed(2)
        },

        /*
        * 计算主、辅商品总金额
        * */
        _calcTotalMainAndCounts: function (data, types) {
          var len = data.length,
            account = 0;
          let multiple = 100;
          for (let i = 0; i < len; i++) {
            if (data[i].selectStatus && data[i].isMainGoods==types) {
              account += data[i].counts * multiple * Number(data[i].currentPrice) * multiple;
            }
          }
          return {
            account: (account / (multiple * multiple)).toFixed(2)
          }
        },
   
        /*根据类型获取每种优惠内容*/
        getMainPromotionTypeInfo: function (data, types, accountMain) {
          var PromotionList = [],
            tempList='', 
          len = data.length;
          for (let i = 0; i < len; i++) {
            if (data[i].type == types) {
              PromotionList.push(data[i]);
            }
          }
          PromotionList.sort(function (a, b) {
            return a.money - b.money;
          });
          for (var key in PromotionList) {
            if (accountMain >= PromotionList[key].money) {
              tempList = PromotionList[key];
            }
          }
          return tempList;
        },

        /*根据类型获取优惠价格*/
        getMainPromotionTypePrice: function (data) {
          var tempPrice=1,
          len = data.length;
          for (let i = 0; i < len; i++) {
            if (data[i].type == 1) {
              tempPrice = (data[i].expression / 100).toFixed(2);
            }
          }
          return tempPrice;
        },

        /*计算商品总重量*/
        getResultweight: function (data) {
          var len = data.length,
            weight = 0;
          for (let i = 0; i < len; i++) {
            weight += parseInt(data[i].weight) * data[i].counts / 1000;
          }
          return (weight).toFixed(2);
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

        /*下单和付款*/
        pay:function(){
            if(!this.data.addressInfo){
                this.showTips('下单提示','请填写您的收货地址');
                return;
            }
            if(this.data.orderStatus==0){
                this._firstTimePay();
            }else{
                this._oneMoresTimePay();
            }
        },

        /*第一次支付*/
        _firstTimePay:function(){
          var orderInfo = {},
              goodsArr=[],
              PromoArr = [],
              procuctInfo=this.data.productsArr,
              PromotionInfo=this.data.PromotionInfo,
              order=new Order();
              for(let i=0;i<procuctInfo.length;i++){
                goodsArr.push({
                    goods_id: procuctInfo[i].goods_id,
                    quantity:procuctInfo[i].counts,
                    option_id: procuctInfo[i].option_id
                })
              }
              if (PromotionInfo){
                for (let i = 0; i < PromotionInfo.length; i++) {
                  PromoArr.push({
                      id: PromotionInfo[i].id,
                  })
                }
              }
              orderInfo = {
                  goodsArrInfo: goodsArr,
                  mainGoodsPrice: this.data.mainGoodsPrice,
                  otherGoodsPrice: this.data.otherGoodsPrice,
                  shippingPrice: this.data.shippingPrice,
                  transId: this.data.shippingtransId,
                  promotionId: PromoArr,
              };
            var that=this;
            //支付分两步，第一步是生成订单号，然后根据订单号支付
            order.doOrder(orderInfo,(data)=>{
                //订单生成成功
                if(data.pass) {
                    //更新订单状态
                    var id=data.order_id;
                    that.data.id=id;
                    that.data.fromCartFlag=false;
                    //开始支付
                    that._execPay(id);
                }else{
                    that._orderFail(data);  // 下单失败
                }

            });
        },

        /*
        * 提示窗口
        * params:
        * title - {string}标题
        * content - {string}内容
        * flag - {bool}是否跳转到 "我的页面"
        */
        showTips:function(title,content,flag){
            wx.showModal({
                title: title,
                content: content,
                showCancel:false,
                success: function(res) {
                    if(flag) {
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

        /*
        *下单失败
        * params:
        * data - {obj} 订单结果信息
        * */
        _orderFail: function (data) {
          var nameArr = [],
            name = '',
            str = '',
            pArr = data.pStatusArray;
          for (let i = 0; i < pArr.length; i++) {
            if (!pArr[i].haveStock) {
              name = pArr[i].name;
              if (name.length > 15) {
                name = name.substr(0, 12) + '...';
              }
              nameArr.push(name);
              if (nameArr.length >= 2) {
                break;
              }
            }
          }
          str += nameArr.join('、');
          if (nameArr.length > 2) {
            str += ' 等';
          }
          str += ' 缺货';
          wx.showModal({
            title: '下单失败',
            content: str,
            showCancel: false,
            success: function (res) {

            }
          });
        },

        /* 再次次支付*/
        _oneMoresTimePay:function(){
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
              that.deleteProducts(); //将已经下单的商品从购物车删除
              wx.navigateTo({
                  url: '../pay-result/pay-result?id=' + id + '&from=order'
              });
            });
        },

        //将已经下单的商品从购物车删除
        deleteProducts:function() {
            var ids=[],arr=this.data.productsArr;
            for(let i=0;i<arr.length;i++){
              ids.push({
                id: arr[i].goods_id,
                guid:arr[i].option_id
              });
            }
            cart.delete(ids);
        },
    }
)