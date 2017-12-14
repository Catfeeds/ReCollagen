<?php
namespace osc\admin\controller;
use osc\common\controller\AdminBase;
use think\Db;
class Banner extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','商品');
		$this->assign('breadcrumb2','Banner');
	}
	
    public function index(){     	
		
		$list = Db::name('banner')->alias('b')->field('b.*,g.name AS goods_name')
			->join('__GOODS__ g','g.goods_id = b.goods_id','left')
			->order('sort')
			->paginate(config('page_num'));

		$this->assign('empty', '<tr><td colspan="20">暂无数据</td></tr>');
		$this->assign('list', $list);
	
		return $this->fetch();

	 }
	 public	function add(){
		if(request()->isPost()){
			return $this->single_table_insert('Banner','添加了banner');
		}
		$this->assign('crumbs', '新增');
		$this->assign('action', url('Banner/add'));
		return $this->fetch('edit');
	}
	 public	function edit(){
		if(request()->isPost()){	
			return $this->single_table_update('Banner','修改了banner');
		}
		$this->assign('crumbs', '修改');
		$this->assign('action', url('Banner/edit'));		
		$this->assign('d',Db::name('Banner')->find((int)input('id')));		
		return $this->fetch('edit');
	}
	public	function del(){
		if(request()->isGet()){	
			$r= $this->single_table_delete('Banner','删除了banner');
			if($r){
				$this->redirect('Banner/index');
			}
		}
	}
	//更新排序
	public function update_sort(){
		$data=input('post.');

		$update['banner_id'] = (int)$data['banner_id'];
		$update['sort']      = (int)$data['sort'];
		
		if(Db::name('banner')->update($update)){
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新了banner排序');
			return true;
		}		
	}
}
?>