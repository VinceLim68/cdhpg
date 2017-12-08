<?php

namespace app\phone\model;

use think\Model;

class TEnquiryModel extends Model {
	protected $pk = 'id';
	protected $table = 't_enquiry';
	// 设置当前模型的数据库连接
	protected $connection = 'db_apprsal_cdh';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
// 	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $updateTime = false;
	

	
}
