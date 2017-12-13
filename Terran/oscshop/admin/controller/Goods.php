<?php
namespace osc\admin\controller;
use osc\common\controller\AdminBase;
use think\Db;
class Goods extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','商品');
		$this->assign('breadcrumb2','商品管理');
	}
	/**
	 * 商品列表
	 */
    public function index(){

		$filter=input('param.');
        
		if(isset($filter['type'])&&$filter['type']=='search'){
			//查询列表
			$list=osc_goods()->get_category_goods_list($filter,config('page_num'));
		}else{
			//默认列表
			$list=osc_goods()->get_goods_list($filter,config('page_num'));
		}		

		$this->assign([
			'list'     => $list,
			'status'   => input('get.status/d'),
			'category' => osc_goods()->get_category_tree(),
			'empty'    => '<tr><td colspan="20">没有数据</td></tr>',
		]);
	
		return $this->fetch();

	 }
	/**
	 * 新增商品
	 */
	 public function add(){
		
		if(request()->isPost()){
			$data     = input('post.');
			$model    = osc_model('admin','goods');  	
			$validate = $this->validate($data,'Goods');	
			if($validate!==true){
				return $this->error($validate);	
			}
			
			$res = $model->add_goods($data);		
			if($res){
				storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'新增了商品');		
				$this->success('新增成功！',url('Goods/index'));			
			}else{
				$this->error('新增失败！');			
			}
		}
		
	 	$this->assign('crumbs', '新增');
		$this->assign('action', url('Goods/add'));
		$this->assign('category', osc_goods()->get_category_tree());
	 	return $this->fetch('edit');
	 }
	 /**
	  * 编辑商品基本信息
	  */
	 public function edit_general(){
		if(request()->isPost()){
			$data = input('post.');	

			$validate = $this->validate($data,'Goods');
			if($validate!==true){
				return $this->error($validate);	
			}
			
			$res = Db::name('goods')->update($data,false,true);
			if ($res) {
				storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新商品基本信息');							
				return $this->success('更新成功！',url('Goods/index'));
			}else{
				return $this->error('更新失败！');	
			}
		}

	 	$this->assign([
	 		'crumbs' => '编辑基本信息',
	 		'category' => osc_goods()->get_category_tree(),
	 		'goods' => Db::name('Goods')->find((int)input('id')),
	 	]);	
		
	 	return $this->fetch('general');
	 }

	/**
	  * 商品选项
	  */
	public function edit_option(){
		$this->assign('goods_option',Db::name('goods_option')->where('goods_id',input('id/d'))->order('sort')->select());	
		$this->assign('crumbs', '选项');	
	 	return $this->fetch('option');
	}
	 /**
	  * 商品折扣
	  */
	 public function edit_discount(){		
		$this->assign('goods_discount',Db::name('goods_discount')->where('goods_id',input('id/d'))->order('quantity ASC')->select());	
		$this->assign('crumbs', '折扣');	
	 	return $this->fetch('discount');
	 }
	/**
	  * 商品相册
	  */
	 public function edit_image(){
	 	$this->assign('goods_images',Db::name('goods_image')->where('goods_id',input('id/d'))->order('sort_order asc')->select());	
		$this->assign('crumbs', '商品轮播图');	
	 	return $this->fetch('image');
	 }
	 //商品手机端描述
	 public function edit_mobile(){
	 	$this->assign('mobile_images',Db::name('goods_mobile_description_image')->where('goods_id',input('id'))->order('sort_order asc')->select());	
		$this->assign('crumbs', '详情描述');	
	 	return $this->fetch('mobile');
	 }
	 
	//编辑信息，新增，修改
	public function ajax_eidt(){
		if(request()->isPost()){

			$data       = input('post.');
			$table_name = $data['table'];
			
			if(isset($data[$table_name][$data['key']])){
				$info=$data[$table_name][$data['key']];
			}	
			
			if($table_name=='goods_discount'){
				if(!is_numeric($info['quantity'])||!is_numeric($info['price']))
				return ['error'=>'请输入数字'];
			}
			
			if(isset($data['id'])&&$data['id']!=''){
				//更新
				$info[$data['pk_id']]=(int)$data['id'];				
								
				$r=Db::name($table_name)->update($info,false,true);
				if($r){
					storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新商品'.$data['id']);	
					return ['success'=>'更新成功'];
				}else{
					return ['error'=>'更新失败'];
				}
			}else{
				//新增
				$info['goods_id']=(int)$data['goods_id'];
		
				$r = Db::name($table_name)->insert($info,false,true);
				if($r){
					storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新商品'.$data['goods_id']);						
					return ['success'=>'更新成功','id'=>$r];
				}else{
					return ['error'=>'更新失败'];
				}
			}
		}
	}
	//用于编辑中删除
	public function ajax_del(){
		if(request()->isPost()){
			$data=input('post.');		
			
			if(empty($data['id'])){
				return ['success'=>'删除成功'];
			}
			
			$r=Db::name($data['table'])->delete($data['id']);
			
			if($r){
				return ['success'=>'删除成功'];
			}else{
				return ['error'=>'删除失败'];
			}
		}
	}
	//复制商品 
	public function copy_goods(){
		$id =input('post.');

		$model=osc_model('admin','goods'); 
		 	
		if($id){		
			foreach ($id['id'] as $k => $v) {						
				$model->copy_goods((int)$v);
			}	
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'复制商品');
			
			$data['redirect']=url('Goods/index');	
						
			return $data;
		}
	}
	//删除商品
	function del(){
		
		$model=osc_model('admin','goods'); 
			
		$r=$model->del_goods((int)input('param.id'));	
		
		if($r){
			
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'删除商品'.input('get.id'));
			
			$this->redirect('Goods/index');
			
		}else{
			
			return $this->error('删除失败！',url('Goods/index'));
		}		
		
	}
	//更新状态
	function set_status(){
		$data=input('param.');
		
		$update['goods_id']=(int)$data['id'];
		$update['status']=(int)$data['status'];
		
		if(Db::name('goods')->update($update)){
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新商品状态');
			$this->redirect('Goods/index');
		}
	}	
	//更新价格
	function update_price(){
		$data=input('post.');
		
		$update['goods_id']=(int)$data['goods_id'];
		$update['price']=(float)$data['price'];
		
		if(Db::name('goods')->update($update)){
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新商品价格');
			return true;
		}		
	}
	//更新数量
	function update_quantity(){
		$data=input('post.');
		
		$update['goods_id']=(int)$data['goods_id'];
		$update['quantity']=(int)$data['quantity'];
		
		if(Db::name('goods')->update($update)){
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新商品数量');
			return true;
		}		
	}
	//更新排序
	function update_sort(){
		$data=input('post.');
		
		$update['goods_id']=(int)$data['goods_id'];
		$update['sort_order']=(int)$data['sort'];
		
		if(Db::name('goods')->update($update)){
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新商品排序');
			return true;
		}		
	}
	
}
?>