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
                    //如果符合返现，返现金额增加到用户账户
                    $promotion = json_decode($order['promotion']);
                    if ($promotion) {
                        foreach ($promotion as $v) {
                            if ($v->type == 2) {
                                Db::name('member')->where(['uid'=>$order['uid']])->setInc('mainAccount',$v->free);
                                //写入财务流水
                                $user = Db::name('member')->where('uid', $order['uid'])->find();
                                Db::name('finance_record')->insert(['uid' => $order['uid'],'amount' => $v->free,'balance' => $user['mainAccount'],'addtime' => time(),'reason' => '订单返现（订单号：'.$order['order_num_alias'].'）','rectype' => 1]);
                            }
                        }
                    }
	 			}
	 			Db::commit();
                return $this->success = '操作成功';
	 		}catch (\Exception $e) {
	            Db::rollback();
	            return $this->error = '操作失败';
	        }
	 	}
	 	return $this->success = '操作成功';
	}
}