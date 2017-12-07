<?php
namespace osc\member\controller;
use osc\common\controller\AdminBase;
use think\Db;
class MemberBackend extends AdminBase{
	/**
	 * 初始化
	 */
	protected function _initialize(){
		parent::_initialize();
		$this->assign('breadcrumb1','会员');
		$this->assign('breadcrumb2','会员管理');
	}
	/**
	 * 会员列表
	 */
    public function index(){     	
		$param=input('param.');
		$map = $query = [];
		if(isset($param['condition'])){		
			$map['m.username|m.openId']=['like',"%".trim($param['condition'])."%"];
		}

		$list=[];
		$list=Db::name('member')->alias('m')
			->where($map)
			->order('m.uid desc')
			->paginate(config('page_num'));		

		$this->assign('list',$list);
		$this->assign('empty','<tr><td colspan="20">没有数据~</td></tr>');
		
    	return $this->fetch();
	}
	public function add(){	 	
		if(request()->isPost()){
			$date=input('post.');
			$date['password'] = $date['pwd'];
			$result = $this->validate($date,'Member');	

			if($result!==true){
				return ['error'=>$result];
			}
			$member['username']  =$date['username'];
			$member['password']  =think_ucenter_encrypt($date['password'],config('PWD_KEY'));
			$member['regdate']   =time();
			$member['email']     =$date['email'];
			$member['telephone'] =$date['telephone'];
			
			$uid=Db::name('member')->insert($member,false,true);
			
			if($uid){
				
				Db::name('member_auth_group_access')->insert(['uid'=>$uid,'group_id'=>$date['groupid']]);
				
				storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'新增了会员');
			
				return ['success'=>'新增成功','action'=>'add'];
			}else{
				return ['error'=>'新增失败'];
				
			}
			
		}
		$this->assign('group',Db::name('member_auth_group')->field('id,title')->select());
		$this->assign('crumbs','新增');
	 	return $this->fetch();
	 }
	 /**
	  * 编辑会员
	  */
 	public function edit(){
	 	
		if(request()->isPost()){
		
			$date = input('post.');			
			$member['checked']     = $date['checked'];
			$member['telephone']   = $date['telephone'];
			$member['groupid']     = $date['groupid'];
			$member['update_time'] = time();
			
			if(Db::name('member')->where('uid',$date['uid'])->update($member)){
				
				Db::name('member_auth_group_access')->where('uid',$date['uid'])->update(['group_id'=>$date['groupid']]);
				
				storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'编辑了会员');
				$this->success('编辑成功',url('MemberBackend/index'));
			}else{
				$this->error('编辑失败');
			}
		}
		
		$list=[
			'info'=>Db::name('member')->find(input('param.id')),
			'address'=>Db::name('address')->where('uid',input('param.id'))->select()
		];
		$this->assign('data',$list);
		$this->assign('crumbs','会员资料');
	 	return $this->fetch('info');
	}
	/**
	  * 修改用户账户金额
	  */ 
	public function updateAccount(){
		$data=input('post.');
		$update['uid'] = (int)$data['uid'];
		$update[$data['accountName']] = (float)$data['accountMoney'];
		
		if(Db::name('member')->update($update)){
			storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'更新会员账户金额');
			return true;
		}		
		return false;
	}
	/**
	  * 修改用户状态
	  */ 
	public function set_status(){
		$data=input('param.');
		Db::name('member')->where('uid',$data['uid'])->update(['checked'=>(int)$data['checked']],false,true);		
		
		storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'修改了用户状态');	
		
		$this->redirect('MemberBackend/index');
	}
 
}
?>