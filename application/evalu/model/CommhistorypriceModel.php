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
	    return $this->hasOne('comm','Id','id');
	}
	
}
