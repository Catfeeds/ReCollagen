import {Order} from '../order/order-model.js';
import {Cart} from '../cart/cart-model.js';
import { AdrList } from '../address/list/list-model.js';

var order=new Order();
var cart=new Cart();
var adrList = new AdrList();

Page({
        data: {
            loadingHidden: false,
            orderHidden:true,
            addressInfo:null,
            userRemarks:'',
            mainGoodsPrice: 0,
            otherGoodsPrice: 0,
            shippingPrice: 0,
            promotion1free: 0,
        },

        onLoad: function (options) {
          var that = this;
          adrList.getAddress((res) => {
            if(res){
              var hasInfo = order._isHasThatOne(res);
              adrList.execSetStorageSync(hasInfo.data);
            }
          });
        },

        onShow:function(){
          var that = this;
          cart.getCartDataFromLocal(1, (data) => {
            if (data.promotion1 && data.promotion1 !=''){
              that.setData({
                promotion1free: data.promotion1.free,
              });
            }
            
            that.data.productsArr=[];
            that.setData({
              loadingHidden: true,
              productsArr: data,
              mainGoodsPrice: (that._calcTotalMainAndCounts(data.goodsList, 1).account - that.data.promotion1free).toFixed(2),
              otherGoodsPrice: that._calcTotalMainAndCounts(data.goodsList, 0).account,
            });

            that.setData({
              total: (parseFloat(that.data.mainGoodsPrice) + parseFloat(that.data.otherGoodsPrice)).toFixed(2)
            });

            adrList.getAddressDataFromLocal((res) => {
              res.address = res.province + ',' + res.city + ',' + res.country + ',' + res.address;
              that.setData({
                addressInfo: res
              })
              var weight = that.getResultweight(data.goodsList).weight;
              order.getTransFee(weight, res.address_id, (res) => {
                that.setData({
                  shippingPrice: res.fee,
                  transId: res.transId,
                  transTitle: res.transTitle,
                  dispatchId: res.dispatchId,
                  dispatchTitle: res.dispatchTitle,
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

        /*选择地址*/
        changAddress: function () {
          var that = this;
          wx.navigateTo({
            url: '../address/list/list?type=order'
          });
        },

        /*留言*/
        bindTextAreaBlur: function (e) {
          this.setData({
            userRemarks: e.detail.value
          })
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
            this.setData({
              orderHidden: false,
            });
            this._firstTimePay();
        },

        /*第一次支付*/
        _firstTimePay:function(){
          var orderInfo = {},
              goodsArr=[],
              promotionArr = [],
              procuctInfo = this.data.productsArr.goodsList,
              promotion1 = this.data.productsArr.promotion1,
              promotion2 = this.data.productsArr.promotion2,
              promotion3 = this.data.productsArr.promotion3,
              promotion4 = this.data.productsArr.promotion4,
              order=new Order();
              for(let i=0;i<procuctInfo.length;i++){
                goodsArr.push({
                    goods_id: procuctInfo[i].goods_id,
                    quantity: procuctInfo[i].count,
                    option_id: procuctInfo[i].goods_option_id
                })
              }

              if (promotion1 && promotion1 != ''){
                  promotionArr.push({
                    name: promotion1.name,
                    type: promotion1.type,
                    free: promotion1.free,
                  })
              }
              if (promotion2 && promotion2 != '') {
                promotionArr.push({
                  name: promotion2.name,
                  type: promotion2.type,
                  free: promotion2.free,
                })
              }
              if (promotion3 && promotion3 != '') {
                var shopcount='';
                var freecount = promotion3.free;
                for (let i = 0; i < freecount.length; i++) {
                  if (shopcount==''){
                     shopcount += freecount[i].name + '*' + promotion3.freeCount;
                  }else{
                     shopcount += '|||' + freecount[i].name + '*' + promotion3.freeCount;
                  }
                }
                promotionArr.push({
                  name: promotion3.name,
                  type: promotion3.type,
                  free: shopcount,
                })
              }
              if (promotion4 && promotion4 != '') {
                promotionArr.push({
                  name: promotion4.name,
                  type: promotion4.type,
                  free: promotion4.free,
                })
              }
              
              orderInfo = {
                  address_id: this.data.addressInfo.address_id,
                  goodsArrInfo: goodsArr,
                  mainGoodsPrice: this.data.mainGoodsPrice,
                  otherGoodsPrice: this.data.otherGoodsPrice,
                  shippingPrice: this.data.shippingPrice,
                  transId: this.data.transId,
                  dispatchId: this.data.dispatchId,
                  userRemarks: this.data.userRemarks,
                  promotion: promotionArr,
              };
              
            var that=this;
            //支付分两步，第一步是生成订单号，然后根据订单号支付
            order.doOrder(orderInfo,(data)=>{
                that.setData({
                  orderHidden: true,
                });
                //订单生成成功   
                if(data.pass) {
                  wx.redirectTo({
                    url: '../pay-result/pay-result?id=' + data.order_id + '&from=order'
                  });
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
        }

    }
)