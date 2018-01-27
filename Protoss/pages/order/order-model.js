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

    /*获取默认地址或第一个地址*/
    _isHasThatOne(arr) {
      var item,
        result = {
          index: 0,
          data: arr[0]
        };
      for (let i = 0; i < arr.length; i++) {
        item = arr[i];
        if (item.is_default == 1) {
          result = {
            index: i,
            data: item
          };
          break;
        }
      }
      return result;
    };
}

export {Order};