/**
 * Created by jimmy on 17/03/09.
 */

import {Base} from '../../utils/base.js'

class Order extends Base{

    constructor(){
        super();
    }

    /*下订单*/
    doOrder(param,callback){
        var that=this;
        var allParams = {
            url: 'order',
            type:'post',
            data: param,
            sCallback: function (data) {
                callback && callback(data);
            },
            eCallback:function(){
                }
            };
        this.request(allParams);
    }


    /*
    * 余额、小金库支付
    * params:
    * norderNumber - {int} 订单id
    * */
    execPay(orderNumber,callback){
        var allParams = {
            url: 'pay/pre_order',
            type:'post',
            data:{id:orderNumber},
            sCallback: function (data) {
              callback && callback(data);
            },
            eCallback: function (data) {
              callback && callback(data);
            }
        };
        this.request(allParams);
    }

    /*获得所有订单,pageIndex 从1开始*/
    getOrders(pageIndex,callback){
        var allParams = {
            url: 'order/by_user',
            type:'get',
            sCallback: function (data) {
                callback && callback(data);  //1 未支付  2，已支付  3，已发货，4已支付，但库存不足
             }
        };
        this.request(allParams);
    }

    /*获得所有主商品优惠价*/
    getMainPromotion(callback){
      var allParams = {
        url: 'promotion/all',
        sCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }

    /*获得订单的具体内容*/
    getOrderInfoById(id,callback){
        var that=this;
        var allParams = {
            url: 'order/'+id,
            sCallback: function (data) {
                callback &&callback(data);
            },
            eCallback:function(){
              
            }
        };
        this.request(allParams);
    }
}

export {Order};