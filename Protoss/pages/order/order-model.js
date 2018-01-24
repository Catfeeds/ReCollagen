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
                callback && callback(data);
            }
            };
        this.request(allParams);
    }


    /*
    * 匹配物流公司并计算运费
    * params:
    * weight - 商品总重量(单位：kg)
    * */
    getTransFee(weight,id, callback) {
      console.log(weight)
      console.log(id)
      var allParams = {
        url: 'order/transfee',
        type: 'post',
        data: { weight: weight, address_id: id },
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }
}

export {Order};