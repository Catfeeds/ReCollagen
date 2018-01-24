
import { Base } from '../../utils/base.js'

class UserInfo extends Base{
    constructor() {
        super();
    }

    /*更新注册账号*/
    _updateUserInfo(data, callback) {
      var param = {
        url: 'user/update',
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

    /*上传图片*/
    _updateUploadImage(data, callback) {
      var param = {
        url: 'user/upload',
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
    


    /*获取我的账户所有信息*/
    getUserAccount(callback) {
      var param = {
        url: 'account',
        sCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(param);
    }
}

export { UserInfo }