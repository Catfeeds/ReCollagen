/**
 * Created by jimmy on 17/2/26.
 */
import {Base} from '../../utils/base.js';

class Category extends Base {
    constructor() {
        super();
        this._storageKeyName = 'category';
    }

    /*获得所有分类*/
    getCategoryType(callback) {
        var param = {
            url: 'category/all',
            sCallback: function (data) {
                callback && callback(data);
            }
        };
        this.request(param);
    }

    /*获得某种分类的商品*/
    getProductsByCategory(id,callback) {
        var param = {
          url: 'product/by_category/'+id,
            sCallback: function (data) {
                callback && callback(data);
            }
        };
        this.request(param);
    }


    /*获取分类下商品记录*/
    getCategoryDataFromLocal() {
      var res = wx.getStorageSync(this._storageKeyName);
      if (!res) {
        res = [];
      }
      return res;
    };

    /*加入到记录缓存里*/
    addCategory(item) {
      var categoryData = this.getCategoryDataFromLocal();
      if (!categoryData) {
        categoryData = [];
      }
      var isHadInfo = this._isHasThatOne(item.id);
      if (isHadInfo.index == -1) {
        categoryData.push(item);
      }
      this.execSetStorageSync(categoryData);  //更新本地缓存
      return categoryData;
    };

    /*是否已经存在*/
    _isHasThatOne(id) {
      var item,
        arr = this.getCategoryDataFromLocal(),
        result = { index: -1 };
      for (let i = 0; i < arr.length; i++) {
        item = arr[i];
        if (item.id == id) {
          result = {
            index: i,
            data: item
          };
          break;
        }
      }
      return result;
      
    };

    /*本地缓存 保存／更新*/
    execSetStorageSync(data) {
      wx.setStorageSync(this._storageKeyName, data);
    };

}

export{Category};