/**
 * Created by jimmy on 17/2/26.
 */

import { Base } from '../../utils/base.js';

class Product extends Base {
  constructor() {
    super();
    this._storageKeyName = 'have';
  }

  /*获取产品详情*/
  getDetailInfo(id, callback) {
    var param = {
      url: 'product/' + id,
      sCallback: function (data) {
        callback && callback(data);
      }
    };
    this.request(param);
  }

  /*
    * 获取收藏商品
    */
  getHaveDataFromLocal(flag) {
    var res = wx.getStorageSync(this._storageKeyName);
    if (!res) {
      res = [];
    }
    return res;
  };

  /*本地缓存 保存／更新*/
  execSetStorageSync(data) {
    wx.setStorageSync(this._storageKeyName, data);
  };


  /*
    * 加入收藏商品
    * 如果之前没有样的商品，则直接添加一条新的记录
    * 如果有，则删除该商品
    * @params:
    * item - {obj} 商品对象,
    * */
  add(item) {
    var haveData = this.getHaveDataFromLocal();
    if (!haveData) {
      haveData = [];
    }
    var isHadInfo = this._isHasThatOne(item.goods_id, haveData);
    //新商品
    if (isHadInfo.index == -1) {
      haveData.push(item);
    }
    //已有商品
    else {
      haveData.splice(isHadInfo.index, 1);  //删除数组某一项
    }
    this.execSetStorageSync(haveData);  //更新本地缓存
    return haveData;
  };

  /*收藏中是否已经存在该商品*/
  _isHasThatOne(id, arr) {
    var item,
      result = { index: -1 };
    for (let i = 0; i < arr.length; i++) {
      item = arr[i];
      if (item.goods_id == id) {
        result = {
          index: i,
          data: item
        };
        break;
      }
    }
    return result;
  }

};

export { Product }
