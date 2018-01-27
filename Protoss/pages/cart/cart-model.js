/**
 * Created by jimmy on 17/03/05.
 */
import {Base} from '../../utils/base.js';

/*
* 购物车数据存放在本地，
* 当用户选中某些商品下单购买时，会从缓存中删除该数据，更新缓存
* 当用用户全部购买时，直接删除整个缓存
*
*/
class Cart extends Base{
    constructor(){
        super();
    };

    /*获取购物车*/
    getCartDataFromLocal(type,callback) {
      var param = {
        url: 'cart/preOrder/checked/' + type,
        sCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(param);
    }


    /*
    *获得购物车商品总数目,包括分类和不分类和商品总价
    */
    getCartTotalCounts(data,flag) {
      var arr = data.goodsList,
        account = 0,
        selectedCounts = 0,
        selectedTypeCounts = 0,
        selectedPromotionCounts = 0;

      for (let i = 0; i < arr.length; i++) {
        if (flag) {
          if (arr[i].isChecked == 1) {
            account += arr[i].totalPrice;
            selectedCounts += parseInt(arr[i].count);
            selectedTypeCounts++;
          }
        }
        else
        {
          account += arr[i].totalPrice;
          selectedCounts += parseInt(arr[i].count);
          selectedTypeCounts++;
        }
      }

      if (data.promotion3 && data.promotion3 != '') {
        selectedPromotionCounts = data.promotion3.free.length * data.promotion3.freeCount;
      }

      return {
        selectedCounts: selectedCounts + selectedPromotionCounts,
        selectedTypeCounts: selectedTypeCounts,
        account: (account).toFixed(2)
      }
    };
    

    /*添加商品到购物车*/
    add(goods_id, option_id, count, callback) {
      var allParams = {
        url: 'cart/add',
        type: 'post',
        data: {
          goods_id: goods_id,
          goods_option_id: option_id,
          count: count
        },
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }

    /*点击增加或减少购物车商品数量*/
    addCutCounts(goods_id, option_id, type, callback) {
      var allParams = {
        url: 'cart/setByClick',
        type: 'post',
        data: {
          goods_id: goods_id,
          goods_option_id: option_id,
          type: type
        },
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }

    /*输入数字修改购物车商品数量*/
    addInputCounts(goods_id, option_id, count, callback) {
      var allParams = {
        url: 'cart/setByInput',
        type: 'post',
        data: {
          goods_id: goods_id,
          goods_option_id: option_id,
          count: count
        },
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }


    /*点击设置购物车商品选中状态*/
    selectStatus(goods_id, option_id, callback) {
      var allParams = {
        url: 'cart/setChecked',
        type: 'post',
        data: {
          goods_id: goods_id,
          goods_option_id: option_id
        },
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }


    /*批量设置购物车商品选中状态*/
    selectAllStatus(isCheck, callback) {
      var allParams = {
        url: 'cart/batchSetChecked',
        type: 'post',
        data: {
          isCheck: isCheck,
        },
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }

    /*从购物车删除商品*/
    delete(goods_id, option_id, callback) {
      var allParams = {
        url: 'cart/del',
        type: 'post',
        data: {
          goods_id: goods_id,
          goods_option_id: option_id
        },
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }

    /*清空购物车*/
    delAll(callback) {
      var allParams = {
        url: 'cart/delAll',
        type: 'post',
        sCallback: function (data) {
          callback && callback(data);
        },
        eCallback: function (data) {
          callback && callback(data);
        }
      };
      this.request(allParams);
    }
    
}

export {Cart};