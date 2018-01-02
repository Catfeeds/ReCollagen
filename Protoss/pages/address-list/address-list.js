import { AddressList } from 'address-list-model.js';
var addressList = new AddressList();

Page({
  data:{
    loadingHidden: false,
    addressInfo: []
  },
  onLoad(){
    /*显示收获地址*/
    this._addressInfo();
  },

  onShow: function () {

    /*显示收获地址*/
    this._addressInfo();
  },

  /*显示收获地址*/
  _addressInfo: function () {
    addressList.getAddress((res) => {
      console.log(res)
      this.setData({
        addressInfo: res,
        loadingHidden: true
      })
    });
  },

})