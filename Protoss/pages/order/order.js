import {Order} from '../order/order-model.js';
import {Cart} from '../cart/cart-model.js';
import { AdrList } from '../address/list/list-model.js';

var order=new Order();
var cart=new Cart();
var adrList = new AdrList();

Page({
        data: {
            addressInfo:null,
            shippingPrice:0
        },

        onLoad: function (options) {
          var that = this;
          cart.getCartDataFromLocal(1,(data) => {
            that.setData({
              productsArr: data,
              mainGoodsPrice: (this._calcTotalMainAndCounts(data.goodsList, 1).account - data.promotion1.free - data.promotion4.free).toFixed(2),
              otherGoodsPrice: this._calcTotalMainAndCounts(data.goodsList, 0).account,
              orderStatus: 0
            });
          })
          adrList.execSetStorageSync(0);
        },

        onShow:function(){
          var that = this;

          /*显示收获地址*/
          adrList.getAddressDataFromLocal((id) => {
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
          })

        },


        /*
        * 计算主、辅商品总金额
        * */
        _calcTotalMainAndCounts: function (data, types) {
          var len = data.length,
            account = 0;
          for (let i = 0; i < len; i++) {
            if (data[i].isMainGoods == types) {
              account += data[i].totalPrice;
            }
          }
          return {
            account: (account).toFixed(2)
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
                  address_id: this.data.addressInfo.address_id,
                  transId: this.data.shippingtransId,
                  promotionId: PromoArr,
              };
              
              console.log(orderInfo)
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
        }

    }
)