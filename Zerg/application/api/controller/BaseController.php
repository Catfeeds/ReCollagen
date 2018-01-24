<?php

namespace app\api\controller;


use app\api\service\Token;
use think\Controller;

class BaseController extends Controller
{
    /**
      * 验证是否有用户权限
      */ 
    protected function checkExclusiveScope(){
        Token::needExclusiveScope();
    }
    /**
      * 验证是否有管理员权限
      */ 
    protected function checkSuperScope(){
        Token::needSuperScope();
    }
    /**
      * 验证是否有用户或者管理员权限
      */ 
    protected function checkPrimaryScope(){
        Token::needPrimaryScope();
    }

}