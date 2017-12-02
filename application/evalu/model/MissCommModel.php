<?php

namespace app\evalu\model;

use think\Model;

class MissCommModel extends Model {
	
	protected $pk = 'id';
	protected $table = 'miss_comm';
	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $updateTime = false;
	
	public function __construct() {
	}
	
	
}

