<?php

namespace app\phone\model;

use think\Model;

class TEnquiryModel extends Model {
	protected $pk = 'id';
	protected $table = 't_enquiry';
	// 设置当前模型的数据库连接
	protected $connection = [
	    // 数据库类型
	    'type'        => 'mysql',
	    // 服务器地址
	    'hostname'    => 'localhost',
	    // 数据库名
	    'database'    => 'apprsal_cdh',
	    // 数据库用户名
	    'username'    => 'root',
	    // 数据库密码
	    'password'    => 'root',
	    // 数据库编码默认采用utf8
	    'charset'     => 'utf8',
	    // 数据库调试模式
	    'debug'       => true,
	];
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	protected $autoWriteTimestamp = true;		//自动转化时间戳
	protected $updateTime = false;
	
	
}
