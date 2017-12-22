/**
 * Created by jimmy on 17/3/9.
 */
import {Base} from 'base.js';
import { Config } from 'config.js';

class Address extends Base{
    constructor() {
        super();
    }

    /*获得我自己的收货地址*/
    getAddress(callback){
        var that=this;
        var param={
            url: 'address',
            sCallback:function(res){
                if(res) {
                    callback && callback(res);
                }
            }
        };
        this.request(param);
    }

    /*更新保存地址*/
    submitAddress(data,callback){
        var param={
            url: 'address',
            type:'post',
            data:data,
            sCallback:function(res){
                callback && callback(res);
            },eCallback(res){
            }
        };
        this.request(param);
    }
}

export {Address}