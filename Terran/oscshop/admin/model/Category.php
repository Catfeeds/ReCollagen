<?php
namespace osc\admin\model;
use think\Db;
use think\Model;
class Category extends Model{
	
	public function add($data){
		
		$validate = validate('Category');
		if(!$validate->check($data)){
		    return ['error'=>$validate->getError()];
		}
        $category['pid']              = $data['pid'];
        $category['image']            = $data['image'];
		$category['name']             = $data['name'];
		$category['update_time']      = time();
		
		return $this->save($category,false,true);
		
	}
	
	public function edit($data){
		
		$validate = validate('Category');
		
		if(!$validate->check($data)){
		    return ['error'=>$validate->getError()];
		}
		$cid=(int)$data['id'];

		$category['pid']         = $data['pid'];
        $category['image']       =$data['image'];
		$category['name']        =$data['name'];
		$category['update_time'] =time();
		
		return $this->where('id',$cid)->update($category,false,true);
		
	}

	public function del_category($cid){			
			
		try{
			Db::name('category')->where('id',$cid)->delete();
			return true;
		}catch(Exception $e){
			return false;
		}
	}

	//分类关联数据
	public function category_link_data($cid){
		return [
			'attribute'=>Db::query('SELECT a.name,a.value,a.attribute_id FROM '.config('database.prefix').'attribute a,'.config('database.prefix').'category_to_attribute cta WHERE a.attribute_id=cta.attribute_id AND cta.cid='.$cid),
			'brand'=>Db::query('SELECT b.name,b.brand_id FROM '.config('database.prefix').'brand b,'.config('database.prefix').'category_to_brand ctb WHERE b.brand_id=ctb.brand_id AND ctb.cid='.$cid)
			];
	}
	
}
?>