<?php
namespace osc\admin\controller;
use osc\common\controller\AdminBase;
use think\Db;
class Brand extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','商品');
		$this->assign('breadcrumb2','品牌');
	}
	
    public function index(){     	
		
		$list = Db::name('brand')->paginate(config('page_num'));
		$this->assign('empty', '<tr><td colspan="20">~~暂无数据</td></tr>');
		$this->assign('list', $list);
	
		return $this->fetch();

	 }
	 public	function add(){
		if(request()->isPost()){	
			return $this->single_table_insert('Brand','添加了品牌');
		}
		$this->assign('crumbs', '新增');
		$this->assign('action', url('Brand/add'));
		return $this->fetch('edit');
	}
	 public	function edit(){
		if(request()->isPost()){	
			return $this->single_table_update('Brand','修改了品牌');
		}
		$this->assign('crumbs', '修改');
		$this->assign('action', url('Brand/edit'));		
		$this->assign('d',Db::name('Brand')->find((int)input('id')));		
		return $this->fetch('edit');
	}
	public	function del(){
		if(request()->isGet()){	
			$r= $this->single_table_delete('Brand','删除了品牌');
			if($r){
				$this->redirect('Brand/index');
			}
		}
	}
}
?>