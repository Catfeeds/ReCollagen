
import { Base } from '../../utils/base.js'

class AdrEdit extends Base{
    constructor() {
        super();
    }

    /*保存保存地址*/
    createAddress(data,callback){
        var param={
            url: 'address/create',
            type:'post',
            data:data,
            sCallback:function(res){
                callback && callback(res);
            },
            eCallback(res){
                callback && callback(res);
            }
        };
        this.request(param);
    }

    /*更新保存地址*/
    updateAddress(data, callback) {
      console.log(data)
      var param = {
        url: 'address/update',
        type: 'post',
        data: data,
        sCallback: function (res) {
          callback && callback(res);
        },
        eCallback(res) {
          callback && callback(res);
        }
      };
      this.request(param);
    }

    /*删除地址*/
    delAddress(id, callback) {
      var param = {
        url: 'address/del',
        type: 'post',
        data: { id: id },
        sCallback: function (res) {
          callback && callback(res);
        },
        eCallback(res) {
          callback && callback(res);
        }
      };
      this.request(param);
    }
}

export { AdrEdit }