/**
 * Created by jimmy on 17/03/09.
 */

import {Base} from '../../utils/base.js'

class Transinfo extends Base{

    constructor(){
        super();
    }

    /*获得物流*/
    getTransinfo(id, callback) {
      console.log(id)
      var param = {
        url: 'order/transinfo/' + id,
        sCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(param);
    }
}

export {Transinfo};