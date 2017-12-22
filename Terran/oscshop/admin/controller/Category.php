<?php
namespace osc\admin\controller;
use osc\common\controller\AdminBase;
use think\Db;
class Category extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','商品');
		$this->assign('breadcrumb2','商品分类');
	}
	
    public function index(){
		$list = Db::name('category')->order('sort')->paginate(config('page_num'));
		$this->assign('empty', '<tr><td colspan="20">暂无数据</td></tr>');
		$this->assign('list', $list);
		
		return $this->fetch();   
    }
	
	public function add(){
		
		if(request()->isPost()){
			
			$model=osc_model('admin','category');			
			$result=$model->add(input('post.'));
			
			if(isset($result['error'])){
				return ['error'=>$result['error']];
			}else{
				if($result){								
					storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'新增了商品分类');						
					return ['success'=>'新增成功','action'=>'add'];				
				}else{			
					return ['error'=>'新增失败'];
				}
				
			}
			
		}
		$this->assign('category',osc_goods()->get_category_tree());
		$this->assign('action',url('Category/add'));
		$this->assign('crumbs','新增');
		return $this->fetch('edit');
	}
	
	public function edit(){
		
		if(request()->isPost()){
			
			$model=osc_model('admin','category');
			$result=$model->edit(input('post.'));
			
			if(isset($result['error'])){
				return ['error'=>$result['error']];
			}else{
				if($result){								
					storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'修改了商品分类');						
					return ['success'=>'修改成功','action'=>'edit'];				
				}else{			
					return ['error'=>'修改失败'];
				}
			}
			
		}else{
			$this->assign('cat',Db::name('category')->find((int)input('param.id')));
			$this->assign('action',url('Category/edit'));
			$this->assign('crumbs','修改');
			return $this->fetch('edit');
		}
		
	}
	
	//删除分类
	public function del(){	
			
		$r=osc_model('admin','category')->del_category((int)input('param.id'));	
		
		if($r){
			
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'删除了分类'.input('get.id'));
			
			$this->redirect('Category/index');
			
		}else{
			
			return $this->error('删除失败！',url('Category/index'));
		}		
		
	}
	
	public function autocomplete(){	
		
		$filter_name=input('filter_name');
		
		if (isset($filter_name)) {
			$sql='SELECT id,name FROM '.config('database.prefix')."category where name LIKE'%".$filter_name."%' LIMIT 0,20";
		}else{
			$sql='SELECT id,name FROM '.config('database.prefix')."category LIMIT 0,20";
		
		}
		$results = Db::query($sql);
		$json=[];
		foreach ($results as $result) {
			$json[] = array(
				'category_id' => $result['id'],
				'name'        => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
			);
		}
		return 	$json;
	}
	//更新排序
	public function update_sort(){
		$data=input('post.');
		
		$update['id']=(int)$data['cid'];
		$update['sort']=(int)$data['sort'];
		
		if(Db::name('category')->update($update)){
			
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新了分类排序');
			
			return true;
		}		
	}
}
