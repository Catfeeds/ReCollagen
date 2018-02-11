/**
 * Created by jimmy on 17/03/09.
 */

import {Base} from '../../utils/base.js'

class OrderDetails extends Base{

    constructor(){
        super();
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

    /*
    * 取消订单
    * params:
    * norderNumber - {int} 订单id
    * */
    cancel(orderNumber, callback) {
      var allParams = {
        url: 'order/cancel',
        type: 'post',
        data: { id: orderNumber },
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }

    /*
    * 删除订单
    * params:
    * norderNumber - {int} 订单id
    * */
    orderDel(orderNumber, callback) {
      var allParams = {
        url: 'order/del',
        type: 'post',
        data: { id: orderNumber },
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }

    /*
    * 确认收货
    * params:
    * norderNumber - {int} 订单id
    * */
    receive(orderNumber, callback) {
      var allParams = {
        url: 'order/receive',
        type: 'post',
        data: { id: orderNumber },
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
    getOrders(statusId,pageIndex,callback){
        var allParams = {
            url: 'order/by_user/' + statusId+'/'+pageIndex,
            type:'get',
            sCallback: function (data) {
              callback && callback(data);  //1待付款,2待发货,3已发货,4已收货,5已取消订单
             }
        };
        this.request(allParams);
    }

    /*获得订单的具体内容*/
    getOrderInfo(id,callback){
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

export { OrderDetails};