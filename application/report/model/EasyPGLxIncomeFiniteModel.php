<?php
namespace app\report\model;

use think\Model;

class EasyPGLxIncomeFiniteModel extends Model {
	protected $pk = 'id';
	protected $table = 'LX_INCOME_FINITE';
	// 设置当前模型的数据库连接
	protected $connection =  'EasyPG';
	protected $resultSetType = 'collection'; // 这个设置可以很快把返回数据集转成array
	protected $field = true; // 忽略非数据表字段而不报错
	
	//自定义初始化
	protected function initialize()
	{
	    //需要调用`Model`的`initialize`方法
	    parent::initialize();
	    //TODO:自定义的初始化
	}

   
}