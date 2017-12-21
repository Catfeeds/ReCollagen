<?php
namespace osc\admin\controller;
use osc\common\controller\AdminBase;
use think\Db;
use osc\admin\model\Promotion AS PromotionModel;

/**
 * 促销管理
 */
class Promotion extends AdminBase{
	
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','促销');
		$this->assign('breadcrumb2','促销管理');
		$this->model = new PromotionModel();
	}
	/**
	 * 促销活动列表
	 */
    public function index(){
		$list = $this->model->order('id desc')->paginate(config('page_num'));

		if (count($list) > 0) {
			foreach ($list as $key => $v) {
				$list[$key]['status'] = getPromotionStatus($v['start_time'],$v['end_time']);
			}
		}

		$this->assign('empty', '<tr><td colspan="20">暂无数据</td></tr>');
		$this->assign('list', $list);
		
		return $this->fetch();   
    }
	/**
	 * 新增促销
	 */
	public function add(){
		if(request()->isPost()){
			$data = input('post.');

			$validate = $this->validate($data,'Promotion');
			if ($validate !== true) {
				$this->error($validate);
			}
			$data['start_time'] = strtotime($data['start_time']);
			$data['end_time']   = strtotime($data['end_time']);
			if($data['start_time']>=$data['end_time']){
				$this->error('开始时间不得大于结束时间');
            }

			$result = $this->model->save($data);
			
			if(!$result){	
				$this->error('新增失败');							
			}else{		
				storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'新增了促销管理');	
				$this->success('新增成功',url('Admin/Promotion/index'));
			}
			
		}

		$this->assign('action',url('Promotion/add'));
		$this->assign('crumbs','新增');
		return $this->fetch('edit');
	}
	/**
	 * 编辑促销
	 */
	public function edit(){
		
		if(request()->isPost()){
			$data = input('post.');
			$validate = $this->validate($data,'Promotion');
			if ($validate !== true) {
				$this->error($validate);
			}
			$data['start_time'] = strtotime($data['start_time']);
			$data['end_time']   = strtotime($data['end_time']);
			if($data['start_time']>=$data['end_time']){
				$this->error('开始时间不得大于结束时间');
            }

			$result = $this->model->update($data);
			
			if(!$result){	
				$this->error('修改失败');							
			}else{		
				storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'修改了促销管理');	
				$this->success('修改成功',url('Admin/Promotion/index'));
			}
			
		}else{
			$promotion = $this->model->find((int)input('param.id'));
			$promotion['start_time'] = date('Y-m-d H:i:s',$promotion['start_time']);
			$promotion['end_time']   = date('Y-m-d H:i:s',$promotion['end_time']);

			$this->assign('promotion',$promotion);
			$this->assign('action',url('Promotion/edit'));
			$this->assign('crumbs','修改');
			return $this->fetch('edit');
		}
		
	}
	/**
	 * 删除促销
	 */
	public function del(){	
			
		$result = PromotionModel::destroy((int)input('param.id'));	
		if(!$result){	
			$this->error('删除失败');							
		}else{		
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'删除了促销管理');	
			$this->redirect('Promotion/index');
		}	
		
	}
	
	
}
