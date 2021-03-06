<?php

namespace app\evalu\model;

use think\Model;

class LoginRecordsModel extends Model {
	
	protected $pk = 'id';
	protected $table = 'login_records';
	protected $autoWriteTimestamp = true;		//自动转化时间戳
	// 定义时间戳字段名
	protected $createTime = 'logindate';
	protected $resultSetType = 'collection';
	protected $updateTime = 'logindate';
	
	public function getLogindateAttr($value)
	{
	    return date("Y-m-d H:i:s",$value);
	}
	
}

