/**
 * Created by jimmy on 17/03/09.
 */

import {Base} from '../../utils/base.js'

class Collect extends Base{

    constructor(){
        super();
    }

    /*获得所有收藏*/
    getCollectList(callback){
      var param = {
        url: 'product/collect',
        sCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(param);
    }
}

export {Collect};