import { AdrList } from 'list-model.js';
import { AdrEdit } from '../edit/edit-model.js';
var adrEdit = new AdrEdit();
var adrList = new AdrList();

Page({
  data:{
    currentAddressIndex:-1,
    loadingHidden: false,
    addressInfo: []
  },

  onReady: function () {
    var titleName = this.data.fromType == 'my' ? '地址管理' : ' 选择地址';
    wx.setNavigationBarTitle({
      title: titleName
    });
  },

  onLoad: function (options) {
    this.data.fromType = options.type;

    var titleName = options.type == 'my' ? '地址管理' : ' 选择地址';
    wx.setNavigationBarTitle({
      title: titleName
    });
  },

  onShow: function () {

    /*显示收获地址*/
    this._addressInfo();
  },

  /*显示所有收获地址*/
  _addressInfo: function () {
    adrList.getAddress((res) => {
      this.setData({
        addressInfo: res,
        loadingHidden: true
      })
    });
  },

  /*选择地址*/
  changeAddress: function (event) {
    var id = adrList.getDataSet(event, 'id'),
      index = adrList.getDataSet(event, 'index');
    if (this.data.fromType == 'order')
    {
      this.setData({
        currentAddressIndex: index
      });
      adrList.execSetStorageSync(id);
      wx.navigateBack();
    }
  },

  setDefault: function (event){
    var that = this,
      id = adrList.getDataSet(event, 'id'),
      index = adrList.getDataSet(event, 'index');
    // 遍历所有地址对象设为非默认
      var addressObjects = this.data.addressInfo;
    for (var i = 0; i < addressObjects.length; i++) {
      // 判断是否为当前地址，是则传true
      addressObjects[i].is_default = i == index
    }
    var hasInfo = adrList._isHasThatOne(id, addressObjects);
    if (hasInfo.index != -1) {
      adrEdit.updateAddress(hasInfo.data, (data) => {
        if (data.errorCode != 0) {
          that.showToast(data.msg);
          return;
        }
        that.setData({
          addressInfo: addressObjects
        });
      });
    }
  },

  add: function () {
    wx.navigateTo({
      url: '../edit/edit?type=add'
    });
  },

  /*修改地址*/
  edit: function (event) {
    var that = this,
      id = adrList.getDataSet(event, 'id');
      wx.navigateTo({
        url: '../edit/edit?type=edit&id=' + id
      });
  },

  /*删除收获地址*/
  delete: function (event) {
    var that = this,
      id = adrList.getDataSet(event, 'id'),
      index = adrList.getDataSet(event, 'index');
    this.showTipsReturn('提示', '确认要删除这个地址吗？', (statusConfirm) => {
      if (statusConfirm) {
        adrEdit.delAddress(id, (statusCode) => {
          if (statusCode.errorCode != 0) {
            that.showTips('提示', statusCode.msg);
            return;
          }
          that.showToast(statusCode.msg);
          that.data.addressInfo.splice(index, 1);
          that.setData({
            addressInfo: that.data.addressInfo
          });
        });
      }
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
  * 提示窗口 - 返回值
  * params:
  * title - {string}标题
  * content - {string}内容
  * callback - {bool}返回值
  */
  showTipsReturn: function (title, content, callback) {
    wx.showModal({
      title: title,
      content: content,
      showCancel: true,
      success: function (res) {
        callback && callback(res.confirm);
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