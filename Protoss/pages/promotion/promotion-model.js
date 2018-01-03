/**
 * Created by jimmy on 17/3/24.
 */
import {Base} from '../../utils/base.js'

class Promotion extends Base{
    constructor(){
        super();
    }
    
    /*获取促销方案*/
    getPromotionTxt(callback) {
      var param = {
        url: 'account',
        sCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(param);
    }
}



export { Promotion }