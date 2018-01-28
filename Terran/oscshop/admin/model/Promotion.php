<?php
namespace osc\admin\model;
use think\Db;
use think\Model;

class Promotion extends Model{
    /**
     * @param $data
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * Author: sai
     * DateTime: 2018/1/28 16:52
     * 判断打折和返现优惠力度是否与现有同类型活动冲突
     */
    public function ifConflict($data,$ifInsert = 0){
        if ($ifInsert == 1) {
            $promotions = $this->where(['type'=>$data['type'],'is_show'=>1,'end_time'=>['gt',time()]])->select();
        }else{
            $promotions = $this->where(['type'=>$data['type'],'is_show'=>1,'end_time'=>['gt',time()],'id'=>['neq',$data['id']]])->select();
        }

        if ($promotions) {
            foreach ($promotions as $key => $v) {
                if ($v['type'] == 1) {
                    //打折
                    if ($data['money'] == $v['money']) {
                        $this->error = '已有需满足同等金额的打折活动，请重新填写';return false;
                    }elseif (($data['money'] > $v['money'] && $data['expression'] >= $v['expression']) || ($data['money'] < $v['money'] && $data['expression'] <= $v['expression'])) {
                        $this->error = '设置的折扣值不符逻辑，请参考已设置的打折活动';return false;
                    }
                }elseif($v['type'] == 2){
                    //返现
                    if ($data['money'] == $v['money']) {
                        $this->error = '已有需满足同等金额的返现活动，请重新填写';return false;
                    }elseif (($data['money'] > $v['money'] && $data['expression'] <= $v['expression']) || ($data['money'] < $v['money'] && $data['expression'] >= $v['expression'])) {
                        $this->error = '设置的返现金额不符逻辑，请参考已设置的返现活动';return false;
                    }
                }
            }
        }

        return true;
    }
}
?>