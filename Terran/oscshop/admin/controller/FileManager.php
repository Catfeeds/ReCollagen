<?php
/**
 * 多用户图片管理器(只显示自己目录下的图片)
 * 
 * 图片只能一张张上传
 */
namespace osc\admin\controller;
use osc\common\controller\ImageManager;
class FileManager extends ImageManager{
	

	protected function _initialize(){	
		parent::_initialize();	
		define('UID',osc_service('admin','user')->is_login());		

        if(!UID) 
		exit();       
		
		$this->init('osc'.UID);
		
	}
	
}
?>