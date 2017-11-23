<?php

namespace app\evalu\model;

use think\Model;

class SalesModel extends Model {
	protected $pk = 'id';
	protected $table = 'for_sale_property';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
	public function search($comm) {
	}
}
