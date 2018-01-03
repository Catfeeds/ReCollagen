
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
  getCartDataFromLocal(callback) {
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
  

  /*根据ID获取某个地址或默认地址*/
  _isHasThatOne(id, arr) {
    var item,
      result = { index: -1 };
    for (let i = 0; i < arr.length; i++) {
      item = arr[i];
      if (id != 0) {
        if (item.address_id == id) {
          result = {
            index: i,
            data: item
          };
          break;
        }
      }
      else {
        if (item.is_default == 1) {
          result = {
            index: i,
            data: item
          };
          break;
        }
      }
    }
    return result;
  };

}

export { AdrList }