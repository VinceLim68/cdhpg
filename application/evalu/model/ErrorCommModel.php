<?php

namespace app\evalu\model;

use think\Model;

class ErrorCommModel extends Model {
	
	protected $pk = 'id';
	protected $table = 'error_comm';
	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $updateTime = false;
	
	public function __construct() {
	}
	
	
}

