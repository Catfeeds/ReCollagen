
import { Base } from '../../utils/base.js'
var Bases = new Base();
class UserInfo extends Base{
    constructor() {
        super();
    }

    /*更新注册账号*/
    _updateUserInfo(data, callback) {
      var baseRestUrl = Bases.baseRestUrl.replace('api/v1/', '');
      var updata = {
          uname: data.uname,
            uwecat: data.uwecat,
              usex: data.usex,
                utel: data.utel,
                  uemail: data.uemail,
                    IDcode: data.IDcode,
                      up_name: data.up_name,
                        up_wecat: data.up_wecat,
                          IDcode_pic: data.IDcode_pic.replace(baseRestUrl, ''),
                            IDcode_pic_b: data.IDcode_pic_b.replace(baseRestUrl, ''),
                              IDcode_pic_h: data.IDcode_pic_h.replace(baseRestUrl, ''),
      }
      var param = {
        url: 'user/update',
        type: 'post',
        data: updata,
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