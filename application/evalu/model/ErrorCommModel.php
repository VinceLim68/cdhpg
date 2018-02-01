<?php

namespace app\evalu\model;

use think\Model;

class ErrorCommModel extends Model {
	
	protected $pk = 'id';
	protected $table = 'error_comm';
	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $updateTime = false;
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	

	public function getTypeAttr($value)
	{
	    $status = [1=>'查无小区',2=>'离散过大',3=>'没有数据',4=>'数据偏少'];
	    return $status[$value];
	}
	
	
}

