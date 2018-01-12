<?php
namespace app\evalu\model;

use think\Model;

class CommRelateModel extends Model
{
	protected $pk = 'id';
	protected $table = 'comm_relate';
	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $resultSetType = 'collection';
	protected $updateTime = FALSE;
	protected $field = true; // 忽略非数据表字段而不报错
	

	
}
