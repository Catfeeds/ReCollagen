<?php
namespace osc\admin\model;
use think\Model;
use think\Db;

class Goods extends Model{
	
	protected $autoWriteTimestamp = 'datetime';
	protected $createTime         = 'add_time';
	protected $updateTime         = 'date_modified';

	/**
	 * 新增商品
	 */
	public function add_goods($data){		

		$goods['name']     = $data['name'];
		$goods['image']    = $data['image'];
		$goods['cat_id']   = $data['cat_id'];
		$goods['price']    = (float)$data['price'];
		$goods['quantity'] = (int)$data['quantity'];
		$goods['status']   = $data['status'];
		$goods['add_time'] = date('Y-m-d H:i:s',time());

		
		$goods['weight']   = empty($data['weight'])? '100' : $data['weight'];
		$goods['length']   = empty($data['length'])? '10' : $data['length'];
		$goods['width']    = empty($data['width'])? '10' : $data['width'];
		$goods['height']   = empty($data['height'])? '10' : $data['height'];
		
		$goods_id = $this->insert($goods,false,true);

		if($goods_id){
			try{
				//商品参数	
				if(isset($data['goods_param'])){
					foreach ($data['goods_param'] as $param) {
						if (!empty($param['param_name']) && !empty($param['param_value'])) {
							Db::name('goods_param')->insert(['goods_id'=>(int)$goods_id,'param_name'=>$param['param_name'],'param_value'=>$param['param_value']]);
						}
					}
				}
				//商品选项	
				if(isset($data['goods_option'])){
					foreach ($data['goods_option'] as $option) {
						Db::execute("INSERT INTO " . config('database.prefix'). "goods_option SET goods_id = '" . (int)$goods_id . "', quantity = '" . (int)$option['quantity'] . "', sort = '" . (int)$option['sort'] . "', option_name = '" . $option['option_name'] . "', option_price=".(float)$option['option_price']);
					}
				}
				//折扣
				if(isset($data['goods_discount'])){
					foreach ($data['goods_discount'] as $discount) {
						Db::execute("INSERT INTO " . config('database.prefix'). "goods_discount SET goods_id = '" . (int)$goods_id . "', quantity = '" . (int)$discount['quantity'] . "', price=".(float)$discount['price']);
					}
				}
				//商品轮播图
				if (isset($data['goods_image'])){
					foreach ($data['goods_image'] as $image) {
						Db::execute("INSERT INTO " . config('database.prefix'). "goods_image SET goods_id =" . (int)$goods_id . ",image = '" . $image['image']."',sort_order =" . (int)$image['sort_order']);
					}
				}
				//商品详情图
				if (isset($data['mobile_image'])){
					foreach ($data['mobile_image'] as $mobile_image) {
						Db::execute("INSERT INTO " . config('database.prefix'). "goods_mobile_description_image SET goods_id =" . (int)$goods_id . ", image = '" . $mobile_image['image']."', description = '" . $mobile_image['description'] .  "', sort_order =" . (int)$mobile_image['sort_order']);
					}
				}
				
			    return true;
				
			}catch(Exception $e){
				return false;
			}
		}else{
			return false;
		}
		
	}
	
	public function edit_option($data){
		$id=(int)$data['goods_id'];
		$quantity=0;
		Db::name('goods_option')->where('goods_id',$id)->delete();
		Db::name('goods_option_value')->where('goods_id',$id)->delete();					
		if (isset($data['goods_option'])) {
			foreach ($data['goods_option'] as $goods_option) {
			if ($goods_option['type'] == 'select' || $goods_option['type'] == 'radio' 
			|| $goods_option['type'] == 'checkbox') {						
					
					$option_id=Db::name('goods_option')->insert(['goods_id'=>(int)$id, 'option_id'=>(int)$goods_option['option_id'], 'required'=>(int)$goods_option['required'], 'option_name'=>$goods_option['name'],'type'=>$goods_option['type']],false,true);
											
					if (isset($goods_option['goods_option_value']) && count($goods_option['goods_option_value']) > 0 ) {
							foreach ($goods_option['goods_option_value'] as $goods_option_value) {								
								Db::execute("INSERT INTO ".config('database.prefix')."goods_option_value SET goods_option_id=".(int)$option_id.",goods_id=".(int)$id.",option_id=".(int)$goods_option['option_id'].",image='".(isset($goods_option_value['option_value_image'])?$goods_option_value['option_value_image']:'')."',option_value_id=".(int)$goods_option_value['option_value_id'].",quantity=".(int)$goods_option_value['quantity'].",subtract=".(int)$goods_option_value['subtract'].",price='".(float)$goods_option_value['price']."',price_prefix='".$goods_option_value['price_prefix']."',weight='".(float)$goods_option_value['weight']."',weight_prefix='".$goods_option_value['weight_prefix']."'");	
								$quantity+=$goods_option_value['quantity'];
							} 
					}						
				}			
			}
		}

		if($quantity>0)
		$this->where('goods_id',$id)->update(['quantity'=>$quantity]);

	}

	public function copy_goods($goods_id){
		
		$goods=Db::name('goods')->find($goods_id);	
		if ($goods) {
			$data = $goods;
			
			unset($data['goods_id']);
							
			$data['goods_description'] =Db::name('goods_description')->where('goods_id',$goods_id)->find();
			
			$data['goods_discount'] = Db::name('goods_discount')->where('goods_id',$goods_id)->select();
			
			$data['goods_image'] = Db::name('goods_image')->where('goods_id',$goods_id)->select();
			
			$data['mobile_image'] = Db::name('goods_mobile_description_image')->where('goods_id',$goods_id)->select();
			
			$data['goods_option'] =osc_goods()->get_goods_options($goods_id);
			
			$category = Db::name('goods_to_category')->where('goods_id',$goods_id)->select();
			
			foreach ($category as $k => $v) {
				$data['goods_category'][]=$v['category_id'];
			}
			
			$attribute = Db::name('goods_attribute')->where('goods_id',$goods_id)->select();
			
			foreach ($attribute as $k => $v) {
				$data['goods_attribute'][]=$v['attribute_value_id'];
			}
			
			$brand = Db::name('goods_brand')->where('goods_id',$goods_id)->select();
			
			foreach ($brand as $k => $v) {
				$data['goods_brand'][]=$v['brand_id'];
			}

		
			$this->add_goods($data);
		}
	}
	
	public function del_goods($goods_id){
		
		try{
								
			Db::name('goods')->where('goods_id',$goods_id)->delete();
			Db::name('goods_discount')->where('goods_id',$goods_id)->delete();	
			Db::name('goods_option')->where('goods_id',$goods_id)->delete();	
			
			Db::name('goods_image')->where('goods_id',$goods_id)->delete();
			Db::name('goods_mobile_description_image')->where('goods_id',$goods_id)->delete();
			
			return true;
		}catch(Exception $e){
			return false;
		}
	}
		
}
?>