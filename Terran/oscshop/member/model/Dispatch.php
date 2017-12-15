<?php
namespace osc\member\model;
use think\Model;
use think\Db;

class Dispatch extends Model{

	public function delDispatch($condition){
        return $this->where($condition)->delete();
	}
	/**
	 * 获取货仓信息
	 */
	public function getDispatchInfo($condition){
	    return $this->where($condition)->find();
	}
	/**
	 * 编辑
	 */
	public function updateDispatch($data){
	    return $this->update($data,false,true);
	}
	/**
	 * 新增
	 */
	public function addDispatch($data){
	    return $this->insert($data,false,true);
	}

}