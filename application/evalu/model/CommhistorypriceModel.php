<?php
namespace app\evalu\model;

use think\Model;

class CommhistorypriceModel extends Model
{
	protected $pk = 'id';
	protected $table = 'commhistoryprice';
	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $resultSetType = 'collection';
	protected $updateTime = FALSE;
	protected $field = true; // 忽略非数据表字段而不报错
	
	public function comm()
	{  //建立与comm表的一对一关联
	    return $this->hasOne('comm','comm_id','community_id');
	}
	
	public function relation()
	{  //建立与comm_relate表的一对一关联
	    return $this->hasOne('comm_relate_model','id','rela_id');
	}
	
	public function getCreateTimeAttr($value){
	    return date('Y-m-d', $value);
	}
	
	public function isDuplicate($item,$whichmonth){
	    //按月和小区id、用途查询
// 	    dump($item);
	    if(!isset($item['usage'])){
	        $item['usage'] = '';
	    }
	    if(isset($item['rela_id'])){
	        //利用关联规则id来判断对于细分功能是否重复
	        $find = $this->where('community_id',$item['community_id'])
	                 ->where('usage',$item['usage'])
	                 ->where('rela_id',$item['rela_id'])
	                 ->where($whichmonth)->find();
	    }else{
	        $find = $this->where('community_id',$item['community_id'])
	        ->where('usage',$item['usage'])
	        ->where($whichmonth)->find();
	    }
	    return $find;
	}
    
        
}
