<?php

namespace app\evalu\model;

use think\Model;
use think\Db;

class CommaddressModel extends Model {
	/**
	 * 操作表：小区-地址信息
	 */
	protected $pk = 'id';
	protected $table = 'commaddress';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
}