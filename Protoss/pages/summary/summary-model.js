/**
 * Created by jimmy on 17/3/24.
 */
import {Base} from '../../utils/base.js'

class Summary extends Base{
    constructor(){
        super();
    }
    
    /*获取消费记录*/
    getSummary(page, pageIndex, callback) {
      var param = {
        url: 'finance_record/getSummary/' + page + '/' + pageIndex,
        sCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(param);
    }
}



export { Summary }