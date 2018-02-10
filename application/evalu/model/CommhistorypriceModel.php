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
	    return date('Y-m', $value);
	}
	
	public function isDuplicate($community_id){
	    $find = $this->where('community_id',$community_id)->whereTime('create_time', 'month')->find();
	    return $find;
	}
}
