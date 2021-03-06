import { AdrEdit } from 'edit-model.js';
import { AdrList } from '../list/list-model.js';
var adrEdit = new AdrEdit();
var adrList = new AdrList();

Page({
  data:{
    addressInfo:{
      name:'',
      telephone:'',
      province: '北京市',
      city: '北京市',
      country: '东城区',
      address:'',
      is_default:-1,
    },
    region: ['北京市', '北京市', '东城区'],
    customItem: '全部'
  },

  onReady: function () {
    var titleName = this.data.fromType == 'edit' ? '修改地址' : ' 添加地址';
    wx.setNavigationBarTitle({
      title: titleName
    });
  },
  
  onLoad: function (options) {
    this.data.fromType = options.type;
    this.data.index = options.index;

    var titleName = options.type == 'edit' ? '修改地址' :' 添加地址';
    wx.setNavigationBarTitle({
      title: titleName
    });
    this._loadData();
  },

  /*加载所有数据*/
  _loadData: function (callback) {
    var that = this;
    /*显示收获地址*/
    adrList.getAddress((res) => {
      if (that.data.fromType == 'edit') {
        var data = res[that.data.index];
        that.setData({
          addressInfo: data,
          region: [data.province, data.city, data.country]
        })
      }
    });
  },


  formSubmit(){
    var self = this;
    var phone = self.data.addressInfo.telephone;
    var phonereg = /^((0\d{2,3}-\d{7,8})|(1[357894]\d{9}))$/;

    if (!self.data.addressInfo.name) {
      self.showToast('姓名不能为空');
      return;
    }
    if (!phone) {
      self.showToast('手机号不能为空');
      return;
    }
    if (!phonereg.test(phone)) {
      self.showToast('手机号有误！');
      return;
    }
    if (!self.data.addressInfo.address) {
      self.showToast('地址不能为空');
      return;
    }

    /*保存收货地址*/
    if (this.data.fromType == 'edit')
    {
      adrEdit.updateAddress(self.data.addressInfo, (data) => {
        if (data.errorCode != 0) {
          self.showToast(data.msg);
          return;
        }
        wx.navigateBack();
      });
    }
    else if (this.data.fromType == 'add')
    {
      adrEdit.createAddress(self.data.addressInfo, (data) => {
        if (data.errorCode != 0) {
          self.showToast(data.msg);
          return;
        }
        wx.navigateBack();
      });
    }
  },

  bindName(e){
    this.setData({
      'addressInfo.name' : e.detail.value
    })
  },
  bindPhone(e){
    this.setData({
      'addressInfo.telephone': e.detail.value
    })
  },
  bindDetail(e){
    this.setData({
      'addressInfo.address' : e.detail.value
    })
  },
  bindRegionChange(e){
    this.setData({
      'addressInfo.province': e.detail.value[0],
      'addressInfo.city': e.detail.value[1],
      'addressInfo.country': e.detail.value[2],
      'region' : e.detail.value
    })
  },

  bindRegionChange(e) {
    this.setData({
      'addressInfo.province': e.detail.value[0],
      'addressInfo.city': e.detail.value[1],
      'addressInfo.country': e.detail.value[2],
      'region': e.detail.value
    })
  },
  
  setDefault(e) {
    var setDefault = this.data.addressInfo.is_default==-1?1:-1;
    this.setData({
      'addressInfo.is_default': setDefault,
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