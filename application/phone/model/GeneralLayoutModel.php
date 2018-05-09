<?php

namespace app\phone\model;

use think\Model;

class GeneralLayoutModel extends Model {
	protected $pk = 'layoutid';
	protected $table = 'general_layout';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
}
