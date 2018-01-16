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
        this._storageKeyName='cart';
    };

    /*
    * 获取购物车
    * param
    * flag - {bool} 是否过滤掉不下单的商品
    */
    getCartDataFromLocal(flag){
        var res = wx.getStorageSync(this._storageKeyName);
        if(!res){
            res=[];
        }
        //在下单的时候过滤不下单的商品，
        if(flag){
            var newRes=[];
            for(let i=0;i<res.length;i++){
                if(res[i].selectStatus){
                    newRes.push(res[i]);
                }
            }
            res=newRes;
        }

        return res;
    };

    /*
    *获得购物车商品总数目,包括分类和不分类
    * param:
    * flag - {bool} 是否区分选中和不选中
    * return
    * counts1 - {int} 不分类
    * counts2 -{int} 分类
    */
    getCartTotalCounts(flag){
        var data=this.getCartDataFromLocal(),
            counts1=0,
            counts2=0;
        for(let i=0;i<data.length;i++){
            if (flag){
                if(data[i].selectStatus) {
                    counts1 += data[i].counts;
                    counts2++;
                }
            }else{
                counts1 += data[i].counts;
                counts2++;
            }
        }
        return {
            counts1:counts1,
            counts2:counts2
        };
    };

    /*本地缓存 保存／更新*/
    execSetStorageSync(data){
        wx.setStorageSync(this._storageKeyName,data);
    };


    /*
    * 加入到购物车
    * 如果之前没有样的商品，则直接添加一条新的记录， 数量为 counts
    * 如果有，则只将相应数量 + counts
    * @params:
    * item - {obj} 商品对象,
    * counts - {int} 商品数目,
    * */
    add(item, counts, price, guid){
        var cartData=this.getCartDataFromLocal();
        if(!cartData){
            cartData=[];
        }
        var isHadInfo = this._isHasThatOne(item.goods_id, guid,cartData);
        var optionName = this._MainPromotion(item.options, guid);
        //新商品
        if(isHadInfo.index==-2) {
            item.counts = counts;
            item.currentPrice = price;
            item.option_id = guid;
            item.option_name = optionName;
            item.selectStatus=true;  //默认在购物车中为选中状态
            cartData.push(item);
        }
        //已有商品
        else{
            cartData[isHadInfo.index].counts += counts;
            cartData[isHadInfo.index].currentPrice = price;
            cartData[isHadInfo.index].option_name = optionName;
        }
        this.execSetStorageSync(cartData);  //更新本地缓存
        return cartData;
    };
    

    /*
    * 修改商品数目
    * params:
    * id - {int} 商品id
    * counts -{int} 数目
    * */
    _changeCounts(id, guid, counts, price){
        var cartData=this.getCartDataFromLocal(),
          hasInfo = this._isHasThatOne(id,guid,cartData);
        if(hasInfo.index!=-2){
            if(hasInfo.data.counts>=1){
                cartData[hasInfo.index].counts=counts;
                cartData[hasInfo.index].currentPrice=price;
            }
        }
        this.execSetStorageSync(cartData);  //更新本地缓存
    };

    /*
    * 修改商品数目
    * */
    addCutCounts(id, guid, counts, price){
      this._changeCounts(id, guid, counts, price);
    };

    /*购物车中是否已经存在该单商品和规格商品*/
    _isHasThatOne(id, guid,arr){
        var item,
            result={index:-2};
        for (let i = 0; i < arr.length; i++) {
          item = arr[i];
          if (item.goods_id == id && item.option_id == guid){
            result = {
              index: i,
              data: item
            };
            break;
          }
        }
        return result;
    };

    //获取商品规格名称
    _MainPromotion(options,guid) {
      var item,
          result='';
      for (let i = 0; i < options.length; i++) {
        item = options[i];
        if (item.goods_option_id == guid) {
          result = item.option_name;
          break;
        }
      }
      return result;
    };
    
    /*
    * 删除某些商品
    */
    delete(ids){
        var cartData=this.getCartDataFromLocal();
        for(let i=0;i<ids.length;i++) {
          var id = ids[i].id;
          var guid = ids[i].guid;
          var hasInfo = this._isHasThatOne(id, guid, cartData);
            if (hasInfo.index != -2) {
                cartData.splice(hasInfo.index, 1);  //删除数组某一项
            }
        }
        this.execSetStorageSync(cartData);
    }
}

export {Cart};