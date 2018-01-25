
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
    _updateUploadImage(tempFilePaths,callback) {
      var Bases = new Base();
      wx.showToast({
        icon: "loading",
        title: "正在上传"
      }),
      wx.uploadFile({
        url: Bases.baseRestUrl+'user/upload',
        filePath: tempFilePaths[0],
        name: 'file',
        header: { "Content-Type": "multipart/form-data" },
        formData: {
          'session_token': wx.getStorageSync('token')
        },
        success: function (res) {
          callback && callback(res);
        },
        fail: function (e) {
          wx.showModal({
            title: '提示',
            content: '上传失败',
            showCancel: false
          })
        },
        complete: function () {
          wx.hideToast();
        }
      })
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