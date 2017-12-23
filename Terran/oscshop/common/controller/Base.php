<?php

namespace osc\common\controller;
use think\Controller;

class Base extends controller{
	
	protected function _initialize() {		
		
		if (!is_file(APP_PATH.'database.php')) {
			header('Location:'.request()->domain().'/install');
			die();
		}				
		
		$module=request()->module();
		
		if(!is_module_install($module)){
			die('该模块未安装');
		}
		
		$config =   cache('db_config_data');
		
        if(!$config){        	
            $config =   load_config();					
            cache('db_config_data',$config);
        }
		
        config($config); 
	}
	
}
