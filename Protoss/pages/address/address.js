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
      console.log(res)
      self.setData({
        addressInfo: res.data
      })
    });
  },
  formSubmit(){
    var self = this;
    if (self.data.addressInfo.name && self.data.addressInfo.telephone && self.data.addressInfo.address){
      /*保存收货地址*/
      address.submitAddress(self.data.addressInfo, (data) => {

        console.log(data)

      });
    }
    else
    {
      self.showTips('提示', '请填写完整资料');
    }
  },

  bindName(e){
    this.setData({
      'addressInfo.name' : e.detail.value
    })
  },
  bindPhone(e){
    this.setData({
      'addressInfo.telephone' : e.detail.value
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
  * content - {string}内容
  * flag - {bool}是否跳转到 "我的页面"
  */
  showTips: function (title, content) {
    wx.showModal({
      title: title,
      content: content,
      showCancel: false,
    });
  },
})