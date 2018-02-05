/**
 * Created by jimmy on 17/3/24.
 */
import {Base} from '../../utils/base.js'

class Legalrules extends Base{
    constructor(){
        super();
    }
    
    /*法律法规交易规则*/
    getLegalrulesTxt(callback) {
      var param = {
        url: 'user/deal',
        sCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(param);
    }
}

export { Legalrules }