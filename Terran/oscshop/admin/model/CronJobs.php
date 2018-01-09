<?php
namespace osc\admin\model;
use think\Model;
use think\Db;
/**
 * 定时业务处理
 */
class CronJobs extends Model{
	/**
	 * 管理员登录触发动作
	 */
	public function autoByAdmin(){
//		$this->autoCancelNoPay();
//        $this->autoAppraise();
        $this->autoReceive();
	}

	/**
	 * 自动确认收货
	 */
	public function autoReceive(){
	 	$autoReceiveDays = 7;
	 	$lastDay = date("Y-m-d 00:00:00",strtotime("-".$autoReceiveDays." days"));

	 	$orders = Db::name('order')->where("deliver_time<'".$lastDay."' and order_status=3")->select();

	 	if(!empty($orders)){
	 		Db::startTrans();
		    try{
		 		foreach ($orders as $key => $order){
		 			//修改订单状态
                    Db::name('order')->where(['order_id'=>$order['order_id']])->update(['order_status'=>4,'receive_time'=>date('Y-m-d 00:00:00')]);
	 			}
	 			Db::commit();
                return $this->success = '操作成功';
	 		}catch (\Exception $e) {
	            Db::rollback();
	            return $this->error = '操作成功';
	        }
	 	}
	 	return $this->success = '操作成功';
	}
}