import { UserInfo } from 'userinfo-model.js';
var userInfo = new UserInfo();
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
        loadingHidden: true
      })
    });
  },

  formSubmit(){
    var self = this;
    var phonereg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    var emailreg = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/;
    var cardreg = /^[1-9]\d{7}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}$|^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/;
    
    if (!self.data.userData.uname) {
      self.showToast('姓名不能为空');
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
    if (!self.data.userData.uemail) {
      self.showToast('邮箱不能为空');
      return;
    }
    if (!emailreg.test(self.data.userData.uemail)) {
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
              url: '../home/home'
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
    wx.showActionSheet({
      itemList: ['从相册中选择', '拍照'],
      itemColor: "#f7982a",
      success: function (res) {
        if (!res.cancel) {
          if (res.tapIndex == 0) {
            _this.chooseWxImage('album', index)
          } else if (res.tapIndex == 1) {
            _this.chooseWxImage('camera', index)
          }
        }
      }
    })
  },
  chooseWxImage: function (type, index) {
    let _this = this;
    wx.chooseImage({
      sizeType: ['original', 'compressed'],
      sourceType: [type],
      success: function (res) {
        var tempFilePaths = res.tempFilePaths[0];
        wx.uploadFile({
          url: 'https://wx.edesoft.cn/api/v1/user/upload',
          filePath: tempFilePaths,
          name: 'file',
          formData: {
            'user': 'test'
          },
          success: function (res) {
            var data = res.data
            if (index==1){
              _this.setData({
                'userData.IDcode_pic': tempFilePaths
              })
            }
            if (index == 2) {
              _this.setData({
                'userData.IDcode_pic_b': tempFilePaths
              })
            }
            if (index == 3) {
              _this.setData({
                'userData.IDcode_pic_h': tempFilePaths
              })
            }
          }
        })
      }
    })
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
})