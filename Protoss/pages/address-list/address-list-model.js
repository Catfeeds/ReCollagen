/**
 * Created by jimmy on 17/3/9.
 */
import { Base } from '../../utils/base.js'

class AddressList extends Base {
  constructor() {
    super();
  }

  /*获得我自己的收货地址*/
  getAddress(callback) {
    var that = this;
    var param = {
      url: 'address',
      sCallback: function (res) {
        if (res) {
          callback && callback(res);
        }
      }
    };
    this.request(param);
  }

}

export { AddressList }