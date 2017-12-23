import { Address } from '../../utils/address.js';
var address = new Address();

Page({
  data:{
    addressInfo:{
      name:'',
      telephone:'',
      province: '北京市',
      city: '北京市',
      country: '东城区',
      address:'',
    },
    region: ['北京市', '北京市', '东城区'],
    customItem: '全部'
  },
  onLoad(){
    var self = this;
    /*显示收获地址*/
    address.getAddress((res) => {
      self.setData({
        addressInfo: res,
        region: [res.province, res.city, res.country]
      })
    });
  },
  formSubmit(){
    var self = this;
    var phone = self.data.addressInfo.telephone;
    var myreg = /^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1})|(17[0-9]{1}))+\d{8})$/;
    if (!myreg.test(phone)) {
      if (phone.length != 11) {
        self.showToast('手机号有误！');
        return;
      }
    }
    if (self.data.addressInfo.name && self.data.addressInfo.telephone && self.data.addressInfo.address){
      /*保存收货地址*/
      address.submitAddress(self.data.addressInfo, (data) => {
        if (data.errorCode!=0) {
          self.showToast('更新失败！');
          return;
        } 
        self.showToast('更新成功。');
        wx.navigateBack();
      });
    }
    else
    {
      self.showToast('请填写完整资料');
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