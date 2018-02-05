import { UserInfo } from 'userinfo-model.js';
var userInfo = new UserInfo();
var baseRestUrl = userInfo.baseRestUrl.replace('api/v1/', '');
Page({
  data:{
    userData:{
      uname:'',
      uwecat: '',
      usex: '',
      utel: '',
      uemail:'',
      IDcode: '',
      up_name: '',
      up_wecat: '',
      IDcode_pic: '',
      IDcode_pic_b: '',
      IDcode_pic_h: '',
      legalrules:0,
    },
    loadingHidden: false,
    sexArray: ['男', '女'],
    index:0,
  },
  onLoad: function (options) {
    this.data.fromType = options.type;
    this._loadData();
  },

  /*加载所有数据*/
  _loadData: function (callback) {
    var that = this;
    userInfo.getUserAccount((data) => {
      this.setData({
        index: data.usex == -1 ? 0 : 1,
        userData: data,
        'userData.legalrules': data.checked,
        loadingHidden: true
      })
    });
  },

  formSubmit(){
    var self = this;
    var phonereg = /^((0\d{2,3}-\d{7,8})|(1[35784]\d{9}))$/;
    var emailreg = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
    var cardreg = /^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/;
    
    if (!self.data.userData.uname) {
      self.showToast('姓名不能为空');
      return;
    }
    if (!self.data.userData.uwecat) {
      self.showToast('微信号不能为空');
      return;
    }
    if (!self.data.userData.utel) {
      self.showToast('电话不能为空');
      return;
    }
    if (!phonereg.test(self.data.userData.utel)) {
      self.showToast('电话有误！');
      return;
    }
    if (self.data.userData.uemail && !emailreg.test(self.data.userData.uemail)) {
      self.showToast('邮箱有误！');
      return;
    }
    if (!self.data.userData.IDcode) {
      self.showToast('省份证不能为空');
      return;
    }
    if (!cardreg.test(self.data.userData.IDcode)) {
      self.showToast('省份证有误！');
      return;
    }
    if (!self.data.userData.IDcode_pic) {
      self.showToast('请上传身份证正面照片');
      return;
    }
    if (!self.data.userData.IDcode_pic_b) {
      self.showToast('请上传身份证反面照片');
      return;
    }
    if (!self.data.userData.IDcode_pic_h) {
      self.showToast('请上传手持身份证照片');
      return;
    }
    if (!self.data.userData.up_name) {
      self.showToast('推荐人不能为空');
      return;
    }
    if (!self.data.userData.up_wecat) {
      self.showToast('推荐人微信号不能为空');
      return;
    }
    if (self.data.userData.legalrules==0) {
      self.showToast('请选择代理协议');
      return;
    }

    userInfo._updateUserInfo(self.data.userData, (data) => {
      if (data.errorCode != 0) {
        self.showTips('提示', data.msg);
        return;
      }
      wx.showModal({
        title: '',
        content: '提交成功',
        showCancel: false,
        success: function (res) {
          if (self.data.fromType == 'my'){
            wx.navigateBack();
          }else{
            wx.switchTab({
              url: '../cart/cart'
            })
          }
        }
      });    
    });
  },

  bindName(e){
    this.setData({
      'userData.uname' : e.detail.value
    })
  },
  bindUwecat(e){
    this.setData({
      'userData.uwecat': e.detail.value
    })
  },
  bindUsexChange: function (e) {
    this.setData({
      'userData.usex': e.detail.value == 0 ? -1 : 1,
      index: e.detail.value,
    })
  },
  bindUtel(e) {
    this.setData({
      'userData.utel': e.detail.value
    })
  },
  bindUemail(e) {
    this.setData({
      'userData.uemail': e.detail.value
    })
  },
  bindIDcode(e) {
    this.setData({
      'userData.IDcode': e.detail.value
    })
  },
  bindUpName(e) {
    this.setData({
      'userData.up_name': e.detail.value
    })
  },
  bindUpWecat(e) {
    this.setData({
      'userData.up_wecat': e.detail.value
    })
  },

  chooseImageTap: function (event) {
    let _this = this,
    index = userInfo.getDataSet(event, 'index');
    wx.chooseImage({
      count: 1, // 默认9
      sizeType: ['original', 'compressed'],
      sourceType: ['album', 'camera'],
      success: function (res) {
        var tempFilePaths = res.tempFilePaths;
        userInfo._updateUploadImage(tempFilePaths, (res) => {
          if (res.statusCode != 200) {
            wx.showModal({
              title: '提示',
              content: '上传失败',
              showCancel: false
            })
            return;
          }
          var data = JSON.parse(res.data);
          _this.showToast(data.msg);
          if (1 == index) {
            _this.setData({
              'userData.IDcode_pic': baseRestUrl + data.returnFileUrl,
            })
          }
          if (2 == index) {
            _this.setData({
              'userData.IDcode_pic_b': baseRestUrl + data.returnFileUrl,
            })
          }
          if (3 == index) {
            _this.setData({
              'userData.IDcode_pic_h': baseRestUrl + data.returnFileUrl,
            })
          }
        })
      }
    })
  },

  /*是否同意悦蔻霖智微商代理协议*/
  setLegalrules: function (e) {
    var legalrules = this.data.userData.legalrules;
    this.setData({
      'userData.legalrules': !legalrules,
    })
  },

  /*悦蔻霖智微商代理协议*/
  legalrules: function (e) {
    wx.navigateTo({
      url: '../legalrules/legalrules'
    });
  },

  /*
  * 提示窗口
  * params:
  * title - {string}标题
  * content - {string}内容
  */
  showTips: function (title, content) {
    wx.showModal({
      title: title,
      content: content,
      showCancel: false,
      success: function (res) {
      }
    });
  },

  /*
  * 提示窗口
  * params:
  * title - {string}标题
  */
  showToast: function (title) {
    wx.showToast({
      title: title,
      icon: 'success',
      duration: 2000
    })
  },

  //分享效果
  onShareAppMessage: function () {
    return {
      title: '悦蔻霖智',
      path: 'pages/userinfo/userinfo'
    }
  }

})