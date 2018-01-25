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
      eCallback: function (data) {
        callback && callback(data);
      }
    };
    this.request(allParams);
  }

};

export { Product }
