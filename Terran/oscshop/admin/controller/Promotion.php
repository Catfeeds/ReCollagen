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
            if (isset($data['expression']) && $data['expression'] == '') {
                $this->error('请输入折扣/返现金额');
            }
			$validate = $this->validate($data,'Promotion');
			if ($validate !== true) {
				$this->error($validate);
			}

            if ($data['type'] == 3) {
                //保存赠送商品的数据
                if (!isset($data['free_goods_id'])) {
                    $this->error('请选择赠送商品');
                }
                $free_arr = [];
                foreach ($data['free_goods_id'] as $key => $v) {
                    $free_arr[$key]['goods_id'] = $v;
                }
                $data['expression'] = json_encode($free_arr);
                unset($data['free_goods_id']);
            }elseif($data['type'] == 4){
                $data['expression'] = $data['expression'].','.$data['expression2'];
                unset($data['expression2']);
            }
            $goodsArr =$data['goods_id'];
            unset($data['goods_id']);

			$data['start_time'] = strtotime($data['start_time']);
			$data['end_time']   = strtotime($data['end_time']);
			if($data['start_time']>=$data['end_time']){
				$this->error('开始时间不得大于结束时间');
            }

			$id = Db::name('promotion')->insertGetId($data);
            if(!$id){
				$this->error('新增失败');							
			}else{
                model('Goods')->save(['promotion'.$data['type'].'_id'=>$id],['goods_id'=>['in',$goodsArr]]);
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
            if (isset($data['expression']) && $data['expression'] == '') {
                $this->error('请输入折扣/返现金额');
            }
			$validate = $this->validate($data,'Promotion');
			if ($validate !== true) {
				$this->error($validate);
			}
            if ($data['type'] == 3) {
                //保存赠送商品的数据
                if (!isset($data['free_goods_id'])) {
                    $this->error('请选择赠送商品');
                }
                $free_arr = [];
                foreach ($data['free_goods_id'] as $key => $v) {
                    $free_arr[$key]['goods_id'] = $v;
                }
                $data['expression'] = json_encode($free_arr);
                unset($data['free_goods_id']);
            }elseif($data['type'] == 4){
                $data['expression'] = $data['expression'].','.$data['expression2'];
                unset($data['expression2']);
            }

            $goodsArr =$data['goods_id'];
            unset($data['goods_id']);

            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time']   = strtotime($data['end_time']);
            if($data['start_time']>=$data['end_time']){
                $this->error('开始时间不得大于结束时间');
            }
            $result = Db::name('promotion')->update($data);

            if($result !== false){
                model('Goods')->save(['promotion'.$data['type'].'_id'=>0],['promotion'.$data['type'].'_id'=>$data['id']]);
                model('Goods')->save(['promotion'.$data['type'].'_id'=>$data['id']],['goods_id'=>['in',$goodsArr]]);

                storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'修改了促销管理');
                $this->success('修改成功',url('Admin/Promotion/index'));
			}else{
                $this->error('修改失败');
			}
			
		}else{
			$promotion = $this->model->find((int)input('param.id'));
			$promotion['goods'] = model('Goods')->getPromotionGoods($promotion['id'],$promotion['type']);
			$promotion['start_time'] = date('Y-m-d H:i:s',$promotion['start_time']);
			$promotion['end_time']   = date('Y-m-d H:i:s',$promotion['end_time']);
            if ($promotion['type'] == 3) {
                $promotion['expression'] = model('Goods')->getFreeGoods($promotion['expression']);
            }elseif ($promotion['type'] == 4){
                $expression = explode(',',$promotion['expression']);
                $promotion['expression'] = $expression[0];
                $promotion['expression2'] = $expression[1];
            }
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
	    $id = (int)input('param.id');
        $promotion = PromotionModel::get($id);

        $result = PromotionModel::destroy($id);
		if(!$result){	
			$this->error('删除失败');							
		}else{
            model('Goods')->where(['promotion'.$promotion['type'].'_id'=>$promotion['id']])->update(['promotion'.$promotion['type'].'_id'=>0]);
            storage_user_action(UID,session('user_auth.username'),config('BACKEND_USER'),'删除了促销管理');
			$this->redirect('Promotion/index');
		}	
	}
	/**
     * 促销信息
     */
    public function info(){
        if (request()->isPost()) {
			$data = input('post.');
			$data['update_time'] = time();
			unset($data['send']);

            $result = Db::name('promotion_info')->update($data);
            if ($result) {
                storage_user_action(UID, session('user_auth.username'), config('BACKEND_USER'), '修改了促销信息');
				$this->success('修改成功');
            } else {
				$this->error('修改失败');							
            }
        } else {
            $this->assign('info', Db::name('promotion_info')->find());
            $this->assign('action', url('Promotion/info'));

            return $this->fetch();
        }
    }
    /**
     * 显示选择促销商品弹窗
     */
    public function chooseBox(){
        if (input('type/d') == 1) {
            //选择免费赠送商品
            return $this->fetch('free_box_choose');
        }
        //选择促销商品范围
        return $this->fetch('box_choose');
    }
    /**
     * 返回弹窗需要显示的商品
     */
    public function getChooseGoods(){
        $data = osc_goods()->getChooseGoods();

        return $data;
    }
	
}
