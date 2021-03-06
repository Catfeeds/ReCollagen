/**
 * Created by jimmy on 17/2/26.
 */

// var Base = require('../../utils/base.js').base;
import {Base} from '../../utils/base.js';

class Home extends Base{
    constructor(){
        super();
    }

    /*banner图片信息*/
    getBannerData(callback){
        var param={
            url: 'banner',
            sCallback:function(data){
              callback && callback(data);
            }
        };
        this.request(param);
    }
    /*首页促销方案*/
    getPromotionData(callback){
        var param={
            url: 'promotion/img',
            sCallback:function(data){
                callback && callback(data);
            }
        };
        this.request(param);
    }

    /*首页部分商品*/
    getProductorData(callback){
        var param={
          url: 'product/recent',
            sCallback:function(data){
                callback && callback(data);
            }
        };
        this.request(param);
    }
};

export {Home};