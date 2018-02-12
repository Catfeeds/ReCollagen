<?php

return [
    //  +---------------------------------
    //  微信相关配置
    //  +---------------------------------

    // // 小程序app_id
    // 'app_id' => 'wx75c4fcf7b722b394',
    // // 小程序app_secret
    // 'app_secret' => '02d09b23b827546118ab3b116b8f100a',
    
    // 小程序app_id
    'app_id' => 'wx978529181e602225',
    // 小程序app_secret
    'app_secret' => '5fa6de70b42c9bde411649d1376e7d86',

    // 微信使用code换取用户openid及session_key的url地址
    'login_url' => "https://api.weixin.qq.com/sns/jscode2session?" .
        "appid=%s&secret=%s&js_code=%s&grant_type=authorization_code",

    // 微信获取access_token的url地址
    'access_token_url' => "https://api.weixin.qq.com/cgi-bin/token?" .
        "grant_type=client_credential&appid=%s&secret=%s",


];
