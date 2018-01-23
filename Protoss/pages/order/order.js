import {Order} from '../order/order-model.js';
import {Cart} from '../cart/cart-model.js';
import { AdrList } from '../address/list/list-model.js';

var order=new Order();
var cart=new Cart();
var adrList = new AdrList();

Page({
        data: {
            addressInfo:null
        },

        onLoad: function (options) {
          adrList.execSetStorageSync(0);
        },

        onShow:function(){
          var that = this;
          cart.getCartDataFromLocal(1, (data) => {
            that.setData({
              productsArr: data,
              mainGoodsPrice: (that._calcTotalMainAndCounts(data.goodsList, 1).account - data.promotion1.free - data.promotion4.free).toFixed(2),
              otherGoodsPrice: that._calcTotalMainAndCounts(data.goodsList, 0).account,
              orderStatus:0
            });

            /*显示收获地址*/
            adrList.getAddressDataFromLocal((id) => {
              that._addressInfo(id);
              /*获取商品重量*/
              var weight = that.getResultweight(data.goodsList).weight;
              /*获取运费信息*/
              order.getTransFee(weight, id, (res) => {
                /*设置总参数*/
                that.setData({
                  shippingPrice: res.fee,
                  transId: res.transId,
                  total: (parseFloat(that.data.mainGoodsPrice) + parseFloat(that.data.otherGoodsPrice) + parseFloat(res.fee)).toFixed(2)
                });
              });
            })

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

        /*计算商品总重量*/
        getResultweight: function (data) {
          var len = data.length,
            weight = 0;
          for (let i = 0; i < len; i++) {
            weight += parseInt(data[i].weight) * data[i].count / 1000;
          }
          return {
            weight: (weight).toFixed(2)
          }
        },

        /*下单和付款*/
        pay:function(){
            if(!this.data.addressInfo){
                this.showTips('下单提示','请填写您的收货地址');
                return;
            }
            this._firstTimePay();
        },

        /*第一次支付*/
        _firstTimePay:function(){
          var orderInfo = {},
              goodsArr=[],
              PromoArr = [],
              procuctInfo = this.data.productsArr.goodsList,
              PromotionInfo=this.data.PromotionInfo,
              order=new Order();
              for(let i=0;i<procuctInfo.length;i++){
                goodsArr.push({
                    goods_id: procuctInfo[i].goods_id,
                    quantity: procuctInfo[i].count,
                    option_id: procuctInfo[i].goods_option_id
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
        }

    }
)