<?php

namespace app\phone\model;

use think\Model;

class CPGRecordModel extends Model {
	protected $pk = 'Rid';
	protected $table = 'C_PGRecord';
	// 设置当前模型的数据库连接
	protected $connection =  [
        // 数据库类型
	    'type'        => 'Sqlsrv',
	    // 服务器地址
	    'hostname'    => '192.168.1.3',
	    // 数据库名
	    'database'    => 'Evalue',
	    // 数据库用户名
	    'username'    => 'sa',
	    // 数据库密码
	    'password'    => 'sa',
	    // 数据库编码默认采用utf8
	    'charset'     => 'utf8',
	    // 数据库调试模式
	    'debug'       => true,
	    // 数据库连接端口
	    'hostport'    => '5433',
    ];
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
// 	protected $autoWriteTimestamp = true;		//自动转化时间戳
// 	protected $updateTime = false;
	
	
}
