/**
 * Created by jimmy on 17/2/26.
 */

import { Base } from '../../utils/base.js';

class Product extends Base {
  constructor() {
    super();
  }

  /*获取产品详情*/
  getDetailInfo(id, callback) {
    var param = {
      url: 'product/' + id,
      sCallback: function (data) {
        callback && callback(data);
      }
    };
    this.request(param);
  }

  /*收藏取消商品*/
  doHaveCollect(id, callback) {
    var allParams = {
      url: 'product/collect',
      type: 'post',
      data: {id:id},
      sCallback: function (data) {
        callback && callback(data);
      },
      eCallback: function () {
        callback && callback(data);
      }
    };
    this.request(allParams);
  }

  /*添加商品到购物车*/

  add(goods_id, option_id, count, callback) {
    console.log(goods_id)
    console.log(option_id)
    console.log(count)
    var allParams = {
      url: 'cart/add',
      type: 'post',
      data: { 
        goods_id: goods_id, 
        goods_option_id: option_id,
        count: count
      },
      sCallback: function (data) {
        callback && callback(data);
      },
      eCallback: function () {
        callback && callback(data);
      }
    };
    this.request(allParams);
  }

};

export { Product }
