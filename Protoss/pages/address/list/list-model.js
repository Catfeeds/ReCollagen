
import { Base } from '../../../utils/base.js';

class AdrList extends Base {
  constructor() {
    super();
    this._storageKeyName = 'address';
  }

  /*获得我自己的收货地址*/
  getAddress(callback) {
    var that = this;
    var param = {
      url: 'address',
      sCallback: function (res) {
        if (res) {
          callback && callback(res);
        }
      }
    };
    this.request(param);
  }

  /*获取缓存*/
  getAddressDataFromLocal(callback) {
    wx.getStorage({
      key: this._storageKeyName,
      success: function (res) {
        callback && callback(res.data);
      }
    })
  };

  /*本地缓存 保存／更新*/
  execSetStorageSync(data) {
    wx.setStorage({
      key: this._storageKeyName, 
      data: data
    });
  };

}

export { AdrList }